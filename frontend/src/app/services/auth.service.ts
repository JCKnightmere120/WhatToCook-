import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { Observable, tap } from 'rxjs';
import { environment } from '../../environments/environment';
import { Capacitor } from '@capacitor/core';

export interface AppUser { id: number; name: string; email: string; }
interface AuthResponse { user: AppUser; token: string; message: string; }

@Injectable({ providedIn: 'root' })
export class AuthService {
  private readonly tokenKey = 'whattocook_token';
  private readonly userKey = 'whattocook_user';

  constructor(private http: HttpClient, private router: Router) {}
  private get baseUrl(): string {
    return Capacitor.getPlatform() === 'android' && Capacitor.isNativePlatform()
      ? environment.androidApiBaseUrl
      : environment.apiBaseUrl;
  }

  get isAuthenticated(): boolean { return !!localStorage.getItem(this.tokenKey); }
  get token(): string | null { return localStorage.getItem(this.tokenKey); }
  get user(): AppUser | null {
    const stored = localStorage.getItem(this.userKey);
    return stored ? JSON.parse(stored) as AppUser : null;
  }

  login(email: string, password: string): Observable<AuthResponse> {
    return this.http.post<AuthResponse>(`${this.baseUrl}/login`, { email, password }).pipe(tap(response => this.save(response)));
  }

  register(name: string, email: string, password: string, passwordConfirmation: string): Observable<AuthResponse> {
    return this.http.post<AuthResponse>(`${this.baseUrl}/register`, {
      name, email, password, password_confirmation: passwordConfirmation,
    }).pipe(tap(response => this.save(response)));
  }

  logout(): void {
    localStorage.removeItem(this.tokenKey);
    localStorage.removeItem(this.userKey);
    localStorage.removeItem('whattocook_active_family');
    this.router.navigateByUrl('/auth', { replaceUrl: true });
  }

  private save(response: AuthResponse): void {
    const previous = this.user;
    if (previous?.id !== response.user.id) localStorage.removeItem('whattocook_active_family');
    localStorage.setItem(this.tokenKey, response.token);
    localStorage.setItem(this.userKey, JSON.stringify(response.user));
  }
}
