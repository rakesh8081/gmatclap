import { Component } from '@angular/core';
import { Meta, Title } from '@angular/platform-browser';
import { POSTS } from './blog.data';

@Component({
  selector: 'app-blog',
  templateUrl: './blog.component.html',
  styleUrl: './blog.component.css',
})
export class BlogComponent {
  posts = POSTS;

  constructor(title: Title, meta: Meta) {
    title.setTitle('GMAT & MBA Admissions Blog | GMATclap');
    meta.updateTag({
      name: 'description',
      content: 'GMAT Focus Edition tips, MBA admissions advice and GMATclap student results from Raaj Tomar (IIM Ahmedabad).',
    });
  }
}
