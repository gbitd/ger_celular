import {
  Component,
  ChangeDetectionStrategy,
  inject,
  signal
} from '@angular/core';

import { CommonModule } from '@angular/common';
import { MatTableModule } from '@angular/material/table';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';

import { DeviceService } from '../../core/services/device.service';
import { Device } from '../../core/models/device.model';

@Component({
  template: `
    <div class="container">

      @if (loading()) {
        <div class="loading">
          <mat-spinner></mat-spinner>
        </div>
      }

      @if (error()) {
        <p class="error">Erro ao carregar dispositivos</p>
      }

      @if (!loading() && !error()) {
        <table mat-table [dataSource]="devices()" class="mat-elevation-z2">

          <!-- Nome -->
          <ng-container matColumnDef="name">
            <th mat-header-cell *matHeaderCellDef>Nome</th>
            <td mat-cell *matCellDef="let device">{{ device.name }}</td>
          </ng-container>

          <!-- Localização -->
          <ng-container matColumnDef="location">
            <th mat-header-cell *matHeaderCellDef>Localização</th>
            <td mat-cell *matCellDef="let device">{{ device.location }}</td>
          </ng-container>

          <!-- Em uso -->
          <ng-container matColumnDef="in_use">
            <th mat-header-cell *matHeaderCellDef>Em uso</th>
            <td mat-cell *matCellDef="let device">
              {{ device.in_use ? 'Sim' : 'Não' }}
            </td>
          </ng-container>

          <!-- Data de compra -->
          <ng-container matColumnDef="purchase_date">
            <th mat-header-cell *matHeaderCellDef>Compra</th>
            <td mat-cell *matCellDef="let device">
              {{ device.purchase_date }}
            </td>
          </ng-container>

          <tr mat-header-row *matHeaderRowDef="columns"></tr>
          <tr mat-row *matRowDef="let row; columns: columns"></tr>
        </table>
      }
    </div>
  `,
  styles: [`
    .container {
      padding: 24px;
    }

    .loading {
      display: flex;
      justify-content: center;
      margin-top: 40px;
    }

    .error {
      color: red;
      text-align: center;
    }

    table {
      width: 100%;
    }
  `],
  imports: [
    CommonModule,
    MatTableModule,
    MatProgressSpinnerModule
  ],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class DevicesComponent {
  private deviceService = inject(DeviceService);

  devices = signal<Device[]>([]);
  loading = signal(true);
  error = signal(false);

  columns = ['name', 'location', 'in_use', 'purchase_date'];

  constructor() {
    this.loadDevices();
  }

  private loadDevices() {
    this.loading.set(true);
    this.error.set(false);

    this.deviceService.getDevices().subscribe({
      next: devices => {
        this.devices.set(devices);
        this.loading.set(false);
      },
      error: () => {
        this.error.set(true);
        this.loading.set(false);
      }
    });
  }
}
