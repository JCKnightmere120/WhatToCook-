import { Component } from '@angular/core';
import { AuthService } from '../services/auth.service';
import { ApiService, FamilyInvitation } from '../services/api.service';
import { HouseholdContextService } from '../services/household-context.service';
@Component({ selector: 'app-more', templateUrl: './more.page.html', styleUrls: ['./more.page.scss'], standalone: false })
export class MorePage {
  invitations: FamilyInvitation[] = []; message = '';
  constructor(public auth: AuthService, private api: ApiService, private householdContext: HouseholdContextService) {}
  ionViewWillEnter() { this.api.invitations().subscribe({ next: response => this.invitations = response.invitations }); }
  accept(invitation: FamilyInvitation) { this.api.acceptInvitation(invitation.id).subscribe({ next: ({ family }) => { this.householdContext.select(this.auth.user?.id, family); this.householdContext.refresh(this.auth.user?.id).subscribe({ error: () => undefined }); this.invitations = this.invitations.filter(item => item.id !== invitation.id); this.message = `You joined ${family.name}. Your household pantry is ready.`; }, error: () => this.message = 'Could not accept this invitation.' }); }
}
