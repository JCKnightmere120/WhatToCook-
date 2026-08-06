import { Injectable, Injector } from '@angular/core';
import { HttpErrorResponse, HttpEvent, HttpHandler, HttpInterceptor, HttpRequest } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError } from 'rxjs/operators';
import { AuthService } from './auth.service';

/** Clears an expired local session whenever a protected API request is rejected. */
@Injectable()
export class AuthErrorInterceptor implements HttpInterceptor {
  constructor(private injector: Injector) {}

  intercept(request: HttpRequest<unknown>, next: HttpHandler): Observable<HttpEvent<unknown>> {
    return next.handle(request).pipe(
      catchError((error: HttpErrorResponse) => {
        if (error.status === 401 && request.headers.has('Authorization')) {
          // Resolve lazily to avoid a HttpClient -> interceptor -> AuthService
          // construction cycle; AuthService itself uses HttpClient for login.
          this.injector.get(AuthService).handleUnauthorized();
        }
        return throwError(() => error);
      }),
    );
  }
}
