// Blog topics, types and helpers.
// To write a post, add a Markdown file to content/blog/ (see content/blog/_template.md).

export interface BlogTopic {
  topic: string; // used in the address: gmatclap.com/blog/topic/<topic>
  name: string;
  group: string;
}

// Blog topics, shown as filters on /blog. Each one has its own page.
// Keep the "{ topic: '" format on one line: the build reads it to pre-render topic pages.
export const TOPICS: BlogTopic[] = [
  { topic: 'gmatprep-quant', name: 'GMATprep Quant', group: 'GMAT & tests' },
  { topic: 'gmatprep-verbal', name: 'GMATprep Verbal', group: 'GMAT & tests' },
  { topic: 'gmatprep-di', name: 'GMATprep DI', group: 'GMAT & tests' },
  { topic: 'executive-assessment', name: 'Executive Assessment (EA)', group: 'GMAT & tests' },
  { topic: 'gmat-waiver', name: 'GMAT waiver', group: 'GMAT & tests' },
  { topic: 'us-universities', name: 'US universities', group: 'Universities' },
  { topic: 'uk-universities', name: 'UK universities', group: 'Universities' },
  { topic: 'europe-universities', name: 'Europe universities', group: 'Universities' },
  { topic: 'canada-universities', name: 'Canada universities', group: 'Universities' },
  { topic: 'asia', name: 'Asia', group: 'Universities' },
  { topic: 'singapore', name: 'Singapore', group: 'Universities' },
  { topic: 'indian-b-schools', name: 'Indian B-schools', group: 'Universities' },
  { topic: 'mba', name: 'MBA', group: 'Programs' },
  { topic: 'one-year-mba', name: 'One-year MBA', group: 'Programs' },
  { topic: 'executive-mba', name: 'Executive MBA', group: 'Programs' },
  { topic: 'deferred-mba', name: 'Deferred MBA', group: 'Programs' },
  { topic: 'mim', name: 'MiM', group: 'Programs' },
  { topic: 'phd', name: 'PhD', group: 'Programs' },
  { topic: 'mba-essay', name: 'MBA essay', group: 'Applications' },
  { topic: 'scholarship', name: 'Scholarship', group: 'Applications' },
  { topic: 'admission-consulting-faq', name: 'Admission consulting FAQ', group: 'Applications' },
  { topic: 'success-story', name: 'Success story', group: 'Applications' },
  { topic: 'finance', name: 'Finance', group: 'Careers' },
  { topic: 'consulting', name: 'Consulting', group: 'Careers' },
  { topic: 'technology', name: 'Technology', group: 'Careers' },
  { topic: 'marketing', name: 'Marketing', group: 'Careers' },
  { topic: 'others', name: 'Others', group: 'Careers' },
];

export const TOPIC_GROUPS = ['GMAT & tests', 'Universities', 'Programs', 'Applications', 'Careers'];

export interface BlogPost {
  slug: string;
  title: string;
  date: string; // YYYY-MM-DD
  author: string;
  topics: string[]; // values from TOPICS[].topic
  summary: string; // one or two sentences for the blog list and Google
  image?: string; // optional, path under assets/images/
  body: string; // HTML
}

// The posts themselves are Markdown files in content/blog/ (one file per post).
// scripts/build_blog.py turns them into posts.generated.ts during the build.
import { POSTS } from './posts.generated';
export { POSTS };

export function findPost(slug: string | null): BlogPost | undefined {
  return POSTS.find((p) => p.slug === slug);
}

export function findTopic(topic: string | null): BlogTopic | undefined {
  return TOPICS.find((t) => t.topic === topic);
}

export function topicsOf(post: BlogPost): BlogTopic[] {
  return post.topics.map((t) => findTopic(t)).filter((t): t is BlogTopic => !!t);
}
