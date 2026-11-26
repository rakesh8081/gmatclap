import { Component, Input } from '@angular/core';
import { RESULTS, StudentResult } from './results.data';

// Grid of student result cards. Used on the home page (limit 5) and on /results (all).
@Component({
  selector: 'app-results-grid',
  templateUrl: './results-grid.component.html',
  styleUrls: ['./results-grid.component.css'],
})
export class ResultsGridComponent {
  @Input() limit = 0;

  get results(): StudentResult[] {
    return this.limit > 0 ? RESULTS.slice(0, this.limit) : RESULTS;
  }

  initials(name: string): string {
    return name
      .split(/\s+/)
      .filter(Boolean)
      .slice(0, 2)
      .map((p) => p[0].toUpperCase())
      .join('');
  }
}
