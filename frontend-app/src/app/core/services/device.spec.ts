import { TestBed } from '@angular/core/testing';
import {
  HttpClientTestingModule,
  HttpTestingController
} from '@angular/common/http/testing';

import { DeviceService } from './device.service';
import { Device } from '../models/device.model';

describe('DeviceService', () => {
  let service: DeviceService;
  let httpMock: HttpTestingController;

  const API_URL = 'http://localhost:8080/api/devices';

  beforeEach(() => {
    TestBed.configureTestingModule({
      imports: [HttpClientTestingModule]
    });

    service = TestBed.inject(DeviceService);
    httpMock = TestBed.inject(HttpTestingController);
  });

  afterEach(() => {
    httpMock.verify();
  });

  it('deve listar dispositivos', () => {
    const mockDevices: Device[] = [
      {
        id: 1,
        name: 'iPhone',
        location: 'Escritório',
        purchase_date: '2026-01-01',
        in_use: true,
        created_at: '',
        updated_at: ''
      }
    ];

    service.getDevices().subscribe(devices => {
      expect(devices.length).toBe(1);
      expect(devices[0].name).toBe('iPhone');
    });

    const req = httpMock.expectOne(API_URL);
    expect(req.request.method).toBe('GET');

    req.flush(mockDevices);
  });

  it('deve criar um dispositivo', () => {
    const payload = {
      name: 'Samsung',
      location: 'Casa',
      purchase_date: '2026-01-10'
    };

    service.createDevice(payload).subscribe(device => {
      expect(device.name).toBe('Samsung');
    });

    const req = httpMock.expectOne(API_URL);
    expect(req.request.method).toBe('POST');
    expect(req.request.body).toEqual(payload);

    req.flush({ id: 2, ...payload });
  });

  it('deve alternar status de uso', () => {
    service.toggleUse(1).subscribe();

    const req = httpMock.expectOne(`${API_URL}/1/use`);
    expect(req.request.method).toBe('PATCH');

    req.flush({});
  });

  it('deve excluir um dispositivo', () => {
    service.deleteDevice(1).subscribe();

    const req = httpMock.expectOne(`${API_URL}/1`);
    expect(req.request.method).toBe('DELETE');

    req.flush(null);
  });
});
