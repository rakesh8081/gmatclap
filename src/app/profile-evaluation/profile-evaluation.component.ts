import { Component } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { NgForm } from '@angular/forms';
import { Meta, Title } from '@angular/platform-browser';

interface ProfileForm {
  name: string;
  email: string;
  phone: string;
  city: string;
  degree: string;
  college: string;
  grad_year: string;
  work_years: string;
  role: string;
  industry: string;
  gmat_status: string;
  gmat_score: string;
  target_score: string;
  test_date: string;
  programs: string[];
  countries: string[];
  schools: string;
  intake: string;
  services: string[];
  notes: string;
  consent: boolean;
  website: string; // hidden bot trap, must stay empty
}

type ListField = 'programs' | 'countries' | 'services';

// Free profile evaluation: collects a student's background and goals,
// saved by /api/profile.php and shown under "Profile evaluations" in /api/admin.php.
@Component({
  selector: 'app-profile-evaluation',
  templateUrl: './profile-evaluation.component.html',
  styleUrls: ['./profile-evaluation.component.css'],
})
export class ProfileEvaluationComponent {
  readonly degrees = ['B.Tech / B.E.', 'B.Com', 'BBA / BMS', 'B.Sc', 'B.A.', 'CA / CS / CFA', 'Masters', 'Other'];
  readonly workYears = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10+'];
  readonly industries = ['IT / Software', 'Consulting', 'Finance / Banking', 'Manufacturing / Engineering', 'Healthcare / Pharma', 'Family business', 'Government / Defence', 'Other'];
  readonly programOptions = ['2-year MBA', '1-year MBA', 'Executive MBA', 'Masters in Management', 'Not sure yet'];
  readonly countryOptions = ['India', 'USA', 'UK', 'Europe', 'Canada', 'Singapore', 'Other'];
  readonly serviceOptions = ['GMAT coaching', 'Profile evaluation', 'B-school shortlisting', 'Essays / SOP', 'Interview preparation'];
  readonly intakes: string[];

  form: ProfileForm = this.blank();
  sending = false;
  done = false;
  error = '';

  constructor(private http: HttpClient, title: Title, meta: Meta) {
    const y = new Date().getFullYear();
    this.intakes = [String(y + 1), String(y + 2), String(y + 3), 'Not decided'];
    title.setTitle('Free MBA Profile Evaluation | GMATclap');
    meta.updateTag({
      name: 'description',
      content: 'Get a free MBA profile evaluation from Raaj Tomar (IIM Ahmedabad): GMAT plan, B-school shortlist and application strategy.',
    });
  }

  private blank(): ProfileForm {
    return {
      name: '', email: '', phone: '', city: '',
      degree: '', college: '', grad_year: '',
      work_years: '', role: '', industry: '',
      gmat_status: '', gmat_score: '', target_score: '', test_date: '',
      programs: [], countries: [], schools: '', intake: '',
      services: [], notes: '', consent: false, website: '',
    };
  }

  startAgain(): void {
    this.form = this.blank();
    this.done = false;
    this.error = '';
  }

  isChecked(field: ListField, value: string): boolean {
    return this.form[field].includes(value);
  }

  toggle(field: ListField, value: string, checked: boolean): void {
    const list = this.form[field].filter((v) => v !== value);
    this.form[field] = checked ? [...list, value] : list;
  }

  submit(f: NgForm): void {
    this.error = '';
    if (f.invalid) {
      f.control.markAllAsTouched();
      this.error = 'Please fill in the highlighted fields.';
      return;
    }
    this.sending = true;
    this.http.post<{ ok: boolean; error?: string }>('/api/profile.php', this.form).subscribe({
      next: () => {
        this.sending = false;
        this.done = true;
      },
      error: (e) => {
        this.sending = false;
        this.error = e?.error?.error || 'Something went wrong. Please try again, or call 6301-696274.';
      },
    });
  }
}
