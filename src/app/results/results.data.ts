// GMATclap student results. Source: GMATclap_2026.pdf, "Some of Our Star Performers".
// To add a result: add an entry below, put a square photo (about 360x360 jpg) in
// src/assets/images/results/ and set `photo` to its file name. Leave `photo` out to show initials.

export interface StudentResult {
  name: string;
  score?: number;        // GMAT score; leave out when the result is an admit only
  background?: string;   // education / work experience
  school?: string;       // admit
  photo?: string;        // file name in assets/images/results/
}

export const RESULTS_YEAR = 2026;

export const RESULTS: StudentResult[] = [
  { name: 'Abhishlok', score: 750, background: 'B.Tech, 6 years in IT', photo: 'abhishlok.jpg' },
  { name: 'Shreeya Singh', score: 735, background: 'IIT Bombay, 3 years experience', school: 'Harvard Business School', photo: 'shreeya-singh.jpg' },
  { name: 'Rahul Nawani', score: 715, background: 'IIIT Gwalior, 17 years experience', school: 'NTU Singapore', photo: 'rahul-nawani.jpg' },
  { name: 'Chano Casal', score: 710, background: 'B.A.S.C. (Switzerland), 6 years experience', school: 'HEC Paris', photo: 'chano-casal.jpg' },
  { name: 'Sweta', score: 700, background: 'B.Tech, 3 years experience', school: 'ISB', photo: 'sweta.jpg' },
  { name: 'Anshul Rajput', score: 675 },
  { name: 'Tanu Gupta', school: 'ISB YL' },
];
