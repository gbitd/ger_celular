import {
  Component,
  ChangeDetectionStrategy,
  inject,
  output,
  signal
} from '@angular/core';

import { FormBuilder, Validators, ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';

import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';

import { DeviceService } from '../../core/services/device.service';
import { Device } from '../../core/models/device.model';

@Component({
  selector: 'app-device-form',
  template: `
    <mat-card>
      <h3>Novo dispositivo</h3>

      <form [formGroup]="form" (ngSubmit)="submit()">

        <mat-form-field class="full">
          <input matInput placeholder="Nome" formControlName="name">
        </mat-form-field>

        <mat-form-field class="full">
          <input matInput placeholder="Localização" formControlName="location">
        </mat-form-field>

        <mat-form-field class="full">
          <input
            matInput
            type="date"
            placeholder="Data de compra"
            formControlName="purchase_date"
          >
        </mat-form-field>

        <button
          mat-raised-button
          color="primary"
          [disabled]="form.invalid || loading()"
        >
          Criar
        </button>

        @if (loading()) {
          <mat-spinner diameter="24"></mat-spinner>
        }
      </form>
    </mat-card>
  `,
  styles: [`
    mat-card {
      margin-bottom: 24px;
    }

    .full {
      width: 100%;
    }

    form {
      display: grid;
      gap: 16px;
    }
  `],
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MatCardModule,
    MatProgressSpinnerModule
  ],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class DeviceFormComponent {
  private fb = inject(FormBuilder);
  private deviceService = inject(DeviceService);

  loading = signal(false);

  created = output<Device>();

  form = this.fb.nonNullable.group({
    name: ['', Validators.required],
    location: ['', Validators.required],
    purchase_date: ['', Validators.required]
  });

  submit() {
    if (this.form.invalid) return;

    this.loading.set(true);

    this.deviceService.createDevice(this.form.getRawValue())
      .subscribe({
        next: (device: Device) => {
          this.created.emit(device);
          this.form.reset();
          this.loading.set(false);
        },
        error: () => {
          alert('Erro ao criar dispositivo');
          this.loading.set(false);
        }
      });

  }
}
