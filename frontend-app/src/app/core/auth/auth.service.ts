import { Injectable, inject, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { tap } from 'rxjs';

const API_URL = 'http://localhost:8080/api';

@Injectable({ providedIn: 'root' })
export class AuthService {
  private http = inject(HttpClient);

  token = signal<string | null>(
    localStorage.getItem('token')
  );

  register(name: string, email: string, password: string) {
    return this.http.post<{ token: string }>(
      `${API_URL}/register`,
      { name, email, password }
    ).pipe(
      tap(res => this.setToken(res.token))
    );
  }

  login(email: string, password: string) {
    return this.http.post<{ token: string }>(
      `${API_URL}/login`,
      { email, password }
    ).pipe(
      tap(res => this.setToken(res.token))
    );
  }

  logout() {
    return this.http.post<void>(
      `${API_URL}/logout`,
      {}
    ).pipe(
      tap(() => this.clearToken())
    );
  }

  isAuthenticated() {
    return !!this.token();
  }

  private setToken(token: string) {
    this.token.set(token);
    localStorage.setItem('token', token);
  }

  private clearToken() {
    this.token.set(null);
    localStorage.removeItem('token');
  }
}
