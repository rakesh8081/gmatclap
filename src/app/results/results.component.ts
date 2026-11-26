import { Component } from '@angular/core';
import { Meta, Title } from '@angular/platform-browser';
import { RESULTS_YEAR } from './results.data';

@Component({
  selector: 'app-results',
  templateUrl: './results.component.html',
  styleUrls: ['./results.component.css'],
})
export class ResultsComponent {
  year = RESULTS_YEAR;

  constructor(title: Title, meta: Meta) {
    title.setTitle('GMAT Results & Admits | GMATclap');
    meta.updateTag({
      name: 'description',
      content:
        'GMATclap student results: GMAT scores up to 750 and admits to Harvard Business School, HEC Paris, NTU Singapore and ISB.',
    });
  }
}
