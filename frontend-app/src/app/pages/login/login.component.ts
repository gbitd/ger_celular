import { Component, ChangeDetectionStrategy, inject } from '@angular/core';
import { FormBuilder, Validators, ReactiveFormsModule } from '@angular/forms';
import { Router } from '@angular/router';

import { MatCardModule } from '@angular/material/card';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';

import { AuthService } from '../../core/auth/auth.service';

@Component({
  template: `
    <mat-card>
      <h2>Login</h2>

      <form [formGroup]="form" (ngSubmit)="submit()">
        <mat-form-field class="full">
          <input matInput placeholder="Email" formControlName="email">
        </mat-form-field>

        <mat-form-field class="full">
          <input matInput type="password" placeholder="Senha" formControlName="password">
        </mat-form-field>

        <button mat-raised-button color="primary">
          Entrar
        </button>
      </form>
    </mat-card>
  `,
  styles: [`
    mat-card { max-width: 400px; margin: 40px auto; }
    .full { width: 100%; }
  `],
  imports: [
    ReactiveFormsModule,
    MatCardModule,
    MatInputModule,
    MatButtonModule
  ],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class LoginComponent {
  private fb = inject(FormBuilder);
  private auth = inject(AuthService);
  private router = inject(Router);

  form = this.fb.group({
    email: ['', [Validators.required, Validators.email]],
    password: ['', Validators.required]
  });

  submit() {
    if (this.form.invalid) return;

    this.auth.login(
      this.form.value.email!,
      this.form.value.password!
    ).subscribe({
      next: () => this.router.navigate(['/']),
      error: () => alert('Login inválido')
    });
  }
}
