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
import { DeviceFormComponent } from '../device-form/device-form.component';



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
    MatButtonModule,
    DeviceFormComponent
  ],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class DevicesComponent {
  private fb = inject(FormBuilder);
  private deviceService = inject(DeviceService);

  devices = signal<Device[]>([]);
  loading = signal(false);
  error = signal(false);
  currentPage = signal(1);
  lastPage = signal(1);


  columns = ['name', 'location', 'in_use', 'purchase_date', 'actions'];

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
    this.loadDevices(1);
  }

  clearFilters() {
    this.filtersForm.reset({
      location: '',
      in_use: null,
      from: '',
      to: ''
    });

    localStorage.removeItem(FILTERS_STORAGE_KEY);
    this.loadDevices(1);
  }

  toggleUse(device: Device) {
    this.deviceService.toggleUse(device.id).subscribe({
      next: updated => {
        this.devices.update(list =>
          list.map(d => d.id === updated.id ? updated : d)
        );
      },
      error: () => alert('Erro ao alterar status')
    });
  }

  delete(device: Device) {
    const confirmed = confirm(
      `Deseja realmente excluir "${device.name}"?`
    );

    if (!confirmed) return;

    this.deviceService.deleteDevice(device.id).subscribe({
      next: () => {
        this.devices.update(list =>
          list.filter(d => d.id !== device.id)
        );
      },
      error: () => alert('Erro ao excluir dispositivo')
    });
  }

  onDeviceCreated() {
    this.loadDevices(1);
  }

  public loadDevices(page = 1) {
    this.loading.set(true);
    this.error.set(false);

    const raw = this.filtersForm.value;

    const filters = {
      location: raw.location || undefined,
      in_use: raw.in_use ?? undefined,
      from: raw.from || undefined,
      to: raw.to || undefined
    };

    this.deviceService.getDevices(filters, page).subscribe({
      next: response => {
        this.devices.set(response.data);
        this.currentPage.set(response.meta.current_page);
        this.lastPage.set(response.meta.last_page);
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
