import { Component, Inject, OnInit, PLATFORM_ID } from '@angular/core';
import { isPlatformBrowser } from '@angular/common';

// The real admin area is the server-side page at /api/admin.php (password checked on the server).
// This route only forwards there so old /adminlogin links keep working.
@Component({
  selector: 'app-admin',
  template: '<p style="text-align:center;margin:40px">Opening admin… <a href="/api/admin.php">continue</a></p>',
})
export class AdminComponent implements OnInit {
  constructor(@Inject(PLATFORM_ID) private platformId: object) {}

  ngOnInit(): void {
    if (isPlatformBrowser(this.platformId)) {
      window.location.replace('/api/admin.php');
    }
  }
}
