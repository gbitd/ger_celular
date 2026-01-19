import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Device } from '../models/device.model';

const API_URL = 'http://localhost:8080/api/devices';

export interface DeviceFilters {
  location?: string;
  in_use?: 0 | 1;
  from?: string;
  to?: string;
  page?: number;
}

@Injectable({ providedIn: 'root' })
export class DeviceService {
  private http = inject(HttpClient);

  getDevices(filters: DeviceFilters = {}): Observable<Device[]> {
    let params = new HttpParams();

    Object.entries(filters).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        params = params.set(key, value);
      }
    });

    return this.http.get<Device[]>(API_URL, { params });
  }

  createDevice(device: {
    name: string;
    location: string;
    purchase_date: string;
  }) {
    return this.http.post<Device>(API_URL, device);
  }

  updateDevice(
    id: number,
    device: {
      name: string;
      location: string;
      purchase_date: string;
    }
  ) {
    return this.http.put<Device>(`${API_URL}/${id}`, device);
  }

  toggleUse(id: number) {
    return this.http.patch<Device>(
      `${API_URL}/${id}/use`,
      {}
    );
  }

  deleteDevice(id: number) {
    return this.http.delete<void>(`${API_URL}/${id}`);
  }
}
