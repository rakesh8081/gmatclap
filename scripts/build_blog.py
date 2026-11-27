#!/usr/bin/env python3
"""Turn the Markdown posts in content/blog/ into src/app/blog/posts.generated.ts
and list every blog page to pre-render in prerender-routes.txt.

Runs in the GitHub build before `ng build`. If a post has a mistake, the build
stops with a message saying which file and what to fix, and the live site is
left unchanged.

Usage: python3 scripts/build_blog.py
Needs: pip install markdown pyyaml
"""
import datetime as dt
import html
import json
import re
import sys
from pathlib import Path

import markdown
import yaml

ROOT = Path(__file__).resolve().parent.parent
POSTS_DIR = ROOT / "content" / "blog"
DATA_TS = ROOT / "src" / "app" / "blog" / "blog.data.ts"
OUT_TS = ROOT / "src" / "app" / "blog" / "posts.generated.ts"
ROUTES = ROOT / "prerender-routes.txt"
ASSETS = ROOT / "src" / "assets"

SLUG_RE = re.compile(r"^[a-z0-9]+(?:-[a-z0-9]+)*$")
errors = []


def err(file, msg):
    errors.append(f"{file}: {msg}")


def known_topics():
    text = DATA_TS.read_text(encoding="utf-8")
    return re.findall(r"^\s*\{ topic: '([a-z0-9-]+)'", text, flags=re.M)


def split_front_matter(text):
    if not text.startswith("---"):
        return None, text
    parts = re.split(r"^---\s*$", text, maxsplit=2, flags=re.M)
    if len(parts) < 3:
        return None, text
    return parts[1], parts[2]


def to_html(md_text):
    body = markdown.markdown(
        md_text,
        extensions=["tables", "sane_lists", "attr_list"],
        output_format="html",
    )
    # Links to other sites open in a new tab.
    body = re.sub(
        r'<a href="(https?://(?!(?:www\.)?gmatclap\.com)[^"]+)"',
        r'<a href="\1" target="_blank" rel="noopener"',
        body,
    )
    return body


def main():
    topics = known_topics()
    posts = []

    files = sorted(POSTS_DIR.glob("*.md"))
    for path in files:
        name = path.name
        if name.lower() == "readme.md" or name.startswith("_"):
            continue
        slug = path.stem
        if not SLUG_RE.match(slug):
            err(name, "file name must be lower-case words joined by hyphens, e.g. us-mba-deadlines-2027.md")
            continue

        raw = path.read_text(encoding="utf-8").replace("\r\n", "\n")
        fm_text, body_md = split_front_matter(raw)
        if fm_text is None:
            err(name, "must start with a --- block containing title, date, author, topics and summary (see content/blog/_template.md)")
            continue
        try:
            fm = yaml.safe_load(fm_text) or {}
        except yaml.YAMLError as e:
            err(name, f"the --- block at the top has a formatting mistake: {str(e).splitlines()[0]}. "
                      "Tip: put text containing a colon (:) in double quotes.")
            continue

        if fm.get("draft") is True:
            print(f"skipping draft: {name}")
            continue

        title = str(fm.get("title") or "").strip()
        author = str(fm.get("author") or "GMATclap").strip()
        summary = str(fm.get("summary") or "").strip()
        image = str(fm.get("image") or "").strip().lstrip("/")
        if image.startswith("assets/"):
            image = image[len("assets/"):]

        date = fm.get("date")
        if isinstance(date, (dt.date, dt.datetime)):
            date = date.strftime("%Y-%m-%d")
        date = str(date or "").strip()

        post_topics = fm.get("topics") or []
        if isinstance(post_topics, str):
            post_topics = [t.strip() for t in post_topics.split(",") if t.strip()]

        if not title:
            err(name, "title is missing")
        if not summary:
            err(name, "summary is missing (one or two sentences shown on the blog list and on Google)")
        if not re.match(r"^\d{4}-\d{2}-\d{2}$", date):
            err(name, "date must look like 2026-12-01")
        if not post_topics:
            err(name, "topics is missing; pick at least one from the list in content/blog/_template.md")
        for t in post_topics:
            if t not in topics:
                err(name, f"unknown topic '{t}'. Allowed: {', '.join(topics)}")
        if image and not (ASSETS / image).is_file():
            err(name, f"image '{image}' not found; upload it to src/assets/{image}")
        if not body_md.strip():
            err(name, "the post has no text below the --- block")

        posts.append({
            "slug": slug,
            "title": title,
            "date": date,
            "author": author,
            "topics": post_topics,
            "summary": summary,
            **({"image": image} if image else {}),
            "body": to_html(body_md),
        })

    if errors:
        print("\nBLOG POST PROBLEMS (nothing was published; fix these and commit again):", file=sys.stderr)
        for e in errors:
            print("  - " + e, file=sys.stderr)
            # Shows as a red annotation on the GitHub Actions run page.
            print(f"::error title=Blog post problem::{e}")
        sys.exit(1)

    posts.sort(key=lambda p: (p["date"], p["slug"]), reverse=True)

    OUT_TS.write_text(
        "// GENERATED FILE - do not edit.\n"
        "// Made by scripts/build_blog.py from the Markdown posts in content/blog/.\n"
        "import type { BlogPost } from './blog.data';\n\n"
        "export const POSTS: BlogPost[] = "
        + json.dumps(posts, ensure_ascii=False, indent=2)
        + ";\n",
        encoding="utf-8",
    )

    routes = [f"/blog/{p['slug']}" for p in posts] + [f"/blog/topic/{t}" for t in topics]
    ROUTES.write_text("\n".join(routes) + "\n", encoding="utf-8")

    print(f"{len(posts)} posts, {len(topics)} topics -> {OUT_TS.relative_to(ROOT)}, {ROUTES.name}")
    for p in posts:
        print(f"  {p['date']}  /blog/{p['slug']}  [{', '.join(p['topics'])}]  {html.unescape(p['title'])}")


if __name__ == "__main__":
    main()
