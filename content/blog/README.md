# Writing a blog post for gmatclap.com

Each file in this folder is one blog post. Add a file here and it appears on
[gmatclap.com/blog](https://gmatclap.com/blog) about 3 minutes later.

## Write a new post

1. In this folder, click **Add file → Create new file**.
2. **Name the file.** The name becomes the web address, so use lower-case words joined by
   hyphens and end with `.md`. Example: `us-mba-deadlines-2027.md` becomes
   `gmatclap.com/blog/us-mba-deadlines-2027`.
3. **Copy the template.** Open [`_template.md`](_template.md), click the copy icon (top right of the file),
   and paste it into your new file.
4. **Fill in the top block** (between the two `---` lines): title, date, author, topics, summary.
   The template lists every topic you can use.
5. **Write the post** below the second `---` line. Use the **Preview** tab to check how it looks.
6. Click **Commit changes…** → **Commit changes**.

That's it. Check the post at `gmatclap.com/blog/<file name>` after about 3 minutes.

## Formatting cheat sheet

| You type | You get |
|---|---|
| `## Heading` | a section heading |
| `### Smaller heading` | a smaller heading |
| `**bold**` | **bold** |
| `*italic*` | *italic* |
| `- item` | a bullet point |
| `1. item` | a numbered point |
| `[text](https://example.com)` | a link |
| `[free profile evaluation](/free-profile-evaluation)` | a link to one of our pages |
| `![description](/assets/images/blog/photo.jpg)` | a picture |

Leave an empty line between paragraphs.

## Edit or remove a post

- **Edit:** open the post's file, click the pencil icon, change it, then **Commit changes**.
- **Hide it without deleting:** add the line `draft: true` inside the top block.
- **Delete:** open the file, click **⋯** (top right) → **Delete file** → **Commit changes**.

## Save a draft

Add `draft: true` inside the top block. The file is saved here but not shown on the website.
Remove that line when you're ready to publish.

## Add a picture

1. Go to the [`src/assets/images/blog`](../../src/assets/images/blog) folder.
2. Click **Add file → Upload files**, drag in the picture and click **Commit changes**.
   Use a simple file name like `isb-campus.jpg` (no spaces). Keep pictures under 500 KB.
3. In your post:
   - to show it on the blog list, add `image: images/blog/isb-campus.jpg` in the top block
   - to show it inside the post, write `![ISB campus](/assets/images/blog/isb-campus.jpg)`

## If something goes wrong

If a post has a mistake (a missing title, a topic spelled wrong, a picture that isn't uploaded),
**nothing is published and the website stays exactly as it was.** GitHub emails you, and the
[Actions](../../actions) page shows a red ✗ with a message naming the file and the problem.
Fix it, commit again, and it goes live.

The most common mistake: a title containing a colon (`:`) must be in double quotes,
like `title: "GMAT Focus: what changed"`.
