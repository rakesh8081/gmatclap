import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Meta, Title } from '@angular/platform-browser';
import { BlogPost, BlogTopic, findPost, POSTS, topicsOf } from './blog.data';

// Single blog post at /blog/:slug. Each post is pre-rendered at build time
// (the build workflow lists every slug in prerender-routes.txt).
@Component({
  selector: 'app-blog-post',
  templateUrl: './blog-post.component.html',
  styleUrls: ['./blog-post.component.css'],
})
export class BlogPostComponent implements OnInit {
  post?: BlogPost;
  more: BlogPost[] = [];
  topics: BlogTopic[] = [];

  constructor(private route: ActivatedRoute, private title: Title, private meta: Meta) {}

  ngOnInit(): void {
    this.route.paramMap.subscribe((params) => {
      this.post = findPost(params.get('slug'));
      this.topics = this.post ? topicsOf(this.post) : [];
      const mine = this.topics.map((t) => t.topic);
      this.more = POSTS.filter((p) => p !== this.post)
        .sort((a, b) => Number(b.topics.some((t) => mine.includes(t))) - Number(a.topics.some((t) => mine.includes(t))))
        .slice(0, 3);
      if (this.post) {
        this.title.setTitle(this.post.title + ' | GMATclap');
        this.meta.updateTag({ name: 'description', content: this.post.summary });
        this.meta.updateTag({ property: 'og:title', content: this.post.title });
        this.meta.updateTag({ property: 'og:description', content: this.post.summary });
        this.meta.updateTag({ property: 'og:type', content: 'article' });
      } else {
        this.title.setTitle('Post not found | GMATclap');
      }
    });
  }
}
