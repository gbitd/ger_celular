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
      <h2>Cadastro</h2>

      <form [formGroup]="form" (ngSubmit)="submit()">
        <mat-form-field class="full">
          <input matInput placeholder="Nome" formControlName="name">
        </mat-form-field>

        <mat-form-field class="full">
          <input matInput placeholder="Email" formControlName="email">
        </mat-form-field>

        <mat-form-field class="full">
          <input matInput type="password" placeholder="Senha" formControlName="password">
        </mat-form-field>

        <button mat-raised-button color="primary">
          Criar conta
        </button>
      </form>
    </mat-card>
  `,
  imports: [
    ReactiveFormsModule,
    MatCardModule,
    MatInputModule,
    MatButtonModule
  ],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class RegisterComponent {
  private fb = inject(FormBuilder);
  private auth = inject(AuthService);
  private router = inject(Router);

  form = this.fb.group({
    name: ['', Validators.required],
    email: ['', [Validators.required, Validators.email]],
    password: ['', Validators.required]
  });

  submit() {
    if (this.form.invalid) return;

    const { name, email, password } = this.form.value;

    this.auth.register(name!, email!, password!)
      .subscribe({
        next: () => this.router.navigate(['/']),
        error: () => alert('Erro ao registrar')
      });
  }
}
