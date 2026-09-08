@extends('layouts.admin')
@section('title','Edit Lokasi')
@section('header','Edit Lokasi: '.$officeLocation->name)
@section('content')
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif
<div class="row">
<div class="col-md-5">
    <div class="card">
        <div class="card-header"><h3 class="card-title">Edit - {{ $officeLocation->name }}</h3></div>
        <form method="POST" action="{{ route('admin.office-locations.update',$officeLocation) }}">
            @csrf @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label>Nama Kantor <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name',$officeLocation->name) }}" required>
                    @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="address" class="form-control" rows="2">{{ old('address',$officeLocation->address) }}</textarea>
                </div>
                <div class="row">
                    <div class="col-6 form-group">
                        <label>Latitude <span class="text-danger">*</span></label>
                        <input type="text" id="latitude" name="latitude" class="form-control" value="{{ old('latitude',$officeLocation->latitude) }}" required>
                    </div>
                    <div class="col-6 form-group">
                        <label>Longitude <span class="text-danger">*</span></label>
                        <input type="text" id="longitude" name="longitude" class="form-control" value="{{ old('longitude',$officeLocation->longitude) }}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Radius (meter) <span class="text-danger">*</span></label>
                    <input type="number" id="radius_meter" name="radius_meter" class="form-control" value="{{ old('radius_meter',$officeLocation->radius_meter) }}" min="10" max="1000" required>
                </div>
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active',$officeLocation->is_active)?'checked':'' }}>
                        <label class="custom-control-label" for="is_active">Aktif</label>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('admin.office-locations.index') }}" class="btn btn-default"><i class="fas fa-arrow-left"></i> Kembali</a>
                <button class="btn btn-warning"><i class="fas fa-save"></i> Update</button>
            </div>
        </form>
    </div>
</div>
<div class="col-md-7">
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-map"></i> Peta (drag marker / klik)</h3></div>
        <div class="card-body p-0"><div id="map" style="height:520px;"></div></div>
    </div>
</div>
</div>
@push('js')
<script>
let lat = parseFloat(document.getElementById('latitude').value) || -6.2088;
let lng = parseFloat(document.getElementById('longitude').value) || 106.8456;
let radius = parseInt(document.getElementById('radius_meter').value) || 100;
const map = L.map('map').setView([lat,lng], 16);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
let marker = L.marker([lat,lng], {draggable:true}).addTo(map);
let circle = L.circle([lat,lng], {radius: radius, color:'blue', fillOpacity:0.12}).addTo(map);
function updateInputs(latlng){
    document.getElementById('latitude').value = latlng.lat.toFixed(8);
    document.getElementById('longitude').value = latlng.lng.toFixed(8);
}
marker.on('dragend', e=>{ const ll=e.target.getLatLng(); updateInputs(ll); circle.setLatLng(ll); });
map.on('click', e=>{ marker.setLatLng(e.latlng); circle.setLatLng(e.latlng); updateInputs(e.latlng); });
document.getElementById('radius_meter').addEventListener('input', e=>{ circle.setRadius(parseInt(e.target.value)||100); });
</script>
@endpush
@endsection
