import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Meta, Title } from '@angular/platform-browser';
import { BlogPost, BlogTopic, findTopic, POSTS, TOPIC_GROUPS, TOPICS, topicsOf } from './blog.data';

// /blog lists every post; /blog/topic/:topic lists the posts of one topic.
@Component({
  selector: 'app-blog',
  templateUrl: './blog.component.html',
  styleUrl: './blog.component.css',
})
export class BlogComponent implements OnInit {
  groups = TOPIC_GROUPS.map((g) => ({ name: g, topics: TOPICS.filter((t) => t.group === g) }));
  topic?: BlogTopic;
  unknownTopic = false;
  posts: BlogPost[] = POSTS;
  topicsOf = topicsOf;

  constructor(private route: ActivatedRoute, private title: Title, private meta: Meta) {}

  ngOnInit(): void {
    this.route.paramMap.subscribe((params) => {
      const slug = params.get('topic');
      this.topic = findTopic(slug);
      this.unknownTopic = !!slug && !this.topic;
      const t = this.topic;
      this.posts = t ? POSTS.filter((p) => p.topics.includes(t.topic)) : POSTS;

      if (t) {
        this.title.setTitle(t.name + ' | GMATclap Blog');
        this.meta.updateTag({ name: 'description', content: 'GMATclap articles on ' + t.name + ': GMAT preparation and MBA admissions advice.' });
      } else {
        this.title.setTitle('GMAT & MBA Admissions Blog | GMATclap');
        this.meta.updateTag({
          name: 'description',
          content: 'GMAT Focus Edition tips, MBA admissions advice and GMATclap student results from Raaj Tomar (IIM Ahmedabad).',
        });
      }
    });
  }

  count(t: BlogTopic): number {
    return POSTS.filter((p) => p.topics.includes(t.topic)).length;
  }
}
