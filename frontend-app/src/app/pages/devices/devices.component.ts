import {
  Component,
  ChangeDetectionStrategy,
  inject,
  signal
} from '@angular/core';

import { CommonModule } from '@angular/common';
import { FormBuilder, ReactiveFormsModule } from '@angular/forms';

import { MatTableModule } from '@angular/material/table';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';

import { DeviceService } from '../../core/services/device.service';
import { Device } from '../../core/models/device.model';


const FILTERS_STORAGE_KEY = 'devices-filters';

@Component({
  templateUrl: './devices.html',
  styleUrls: ['./devices.scss'],
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatTableModule,
    MatProgressSpinnerModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatButtonModule
  ],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class DevicesComponent {
  private fb = inject(FormBuilder);
  private deviceService = inject(DeviceService);

  devices = signal<Device[]>([]);
  loading = signal(false);
  error = signal(false);


  columns = ['name', 'location', 'in_use', 'purchase_date'];

  filtersForm = this.fb.group({
    location: [''],
    in_use: [null as 0 | 1 | null],
    from: [''],
    to: ['']
  });

  constructor() {
    this.restoreFilters();
    this.loadDevices();
  }

  applyFilters() {
    this.saveFilters();
    this.loadDevices();
  }

  clearFilters() {
    this.filtersForm.reset({
      location: '',
      in_use: null,
      from: '',
      to: ''
    });

    localStorage.removeItem(FILTERS_STORAGE_KEY);
    this.loadDevices();
  }

  private loadDevices() {
    this.loading.set(true);
    this.error.set(false);

    const raw = this.filtersForm.value;

    const filters = {
      location: raw.location || undefined,
      in_use: raw.in_use ?? undefined,
      from: raw.from || undefined,
      to: raw.to || undefined
    };

    this.deviceService.getDevices(filters).subscribe({
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

  private saveFilters() {
    localStorage.setItem(
      FILTERS_STORAGE_KEY,
      JSON.stringify(this.filtersForm.value)
    );
  }

  private restoreFilters() {
    const stored = localStorage.getItem(FILTERS_STORAGE_KEY);

    if (!stored) return;

    try {
      this.filtersForm.patchValue(JSON.parse(stored));
    } catch {
      localStorage.removeItem(FILTERS_STORAGE_KEY);
    }
  }


}
