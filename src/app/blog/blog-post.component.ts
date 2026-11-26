import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Meta, Title } from '@angular/platform-browser';
import { BlogPost, findPost, POSTS } from './blog.data';

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

  constructor(private route: ActivatedRoute, private title: Title, private meta: Meta) {}

  ngOnInit(): void {
    this.route.paramMap.subscribe((params) => {
      this.post = findPost(params.get('slug'));
      this.more = POSTS.filter((p) => p !== this.post).slice(0, 3);
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
