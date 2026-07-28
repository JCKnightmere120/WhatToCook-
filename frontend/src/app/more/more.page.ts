import { Component } from '@angular/core';
import { AuthService } from '../services/auth.service';
import { ApiService, FamilyInvitation } from '../services/api.service';
@Component({ selector: 'app-more', templateUrl: './more.page.html', styleUrls: ['./more.page.scss'], standalone: false })
export class MorePage {
  invitations: FamilyInvitation[] = []; message = '';
  constructor(public auth: AuthService, private api: ApiService) {}
  ionViewWillEnter() { this.api.invitations().subscribe({ next: response => this.invitations = response.invitations }); }
  accept(invitation: FamilyInvitation) { this.api.acceptInvitation(invitation.id).subscribe({ next: ({ family }) => { localStorage.setItem('whattocook_active_family', String(family.id)); this.invitations = this.invitations.filter(item => item.id !== invitation.id); this.message = `You joined ${family.name}.`; }, error: () => this.message = 'Could not accept this invitation.' }); }
}
