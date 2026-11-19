@extends('layouts.app')
@section('title','Role & Permission - Admin YZA Automotive')
@section('admin-page-title','Role & Permission')
@section('content')
@php($permissionTotal=$permissions->count())
<div class="admin-rbac-page">
    @if(session('success'))
        <div style="margin:0 0 16px;padding:12px 16px;border-radius:12px;background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;font-size:13px;font-weight:600;">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div style="margin:0 0 16px;padding:12px 16px;border-radius:12px;background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;font-size:13px;font-weight:600;">{{ $errors->first() }}</div>
    @endif
    <section class="rbac-hero"><div class="rbac-hero-copy"><span>YZA AUTOMOTIVE</span><h2>Kelola Role &amp; Permission<br>dengan <em>Lebih Aman</em></h2><p>Atur hak akses, pantau aktivitas, dan pastikan setiap pengguna hanya dapat mengakses fitur sesuai dengan perannya.</p></div><div class="rbac-hero-points"><div><b>▣</b><span><strong>Access Control Terpusat</strong><small>Semua hak akses dalam satu sistem</small></span></div><div><b>▥</b><span><strong>Monitoring Permission</strong><small>Pantau konfigurasi akses yang tersedia</small></span></div><div><b>♟</b><span><strong>Struktur Role yang Aman</strong><small>Hierarki role jelas dan terkelola</small></span></div><div><b>▤</b><span><strong>Audit Akses Lebih Jelas</strong><small>Lacak perubahan yang tercatat</small></span></div></div></section>
    <section class="rbac-stats">@foreach([['red','♟','Total Role',$roles->count(),'Role terdaftar dalam sistem'],['blue','▤','Permission Terdaftar',$permissionTotal,'Permission resmi tersedia'],['green','♙','Pengguna Terpetakan',$mappedUsers,'User dengan role assignment'],['amber','◷','Role Perlu Review','Belum tersedia','Workflow review belum tersedia'],['purple','♜','Audit Kebijakan','Belum tersedia','Audit kebijakan khusus belum tersedia']] as [$tone,$icon,$label,$value,$caption])<article class="rbac-stat tone-{{ $tone }}"><span>{{ $icon }}</span><div><small>{{ $label }}</small><strong>{{ $value }}</strong><p>{{ $caption }}</p></div><i>↗</i></article>@endforeach</section>
    <div class="rbac-main-grid">
        <div class="rbac-left-stack">
            <section class="rbac-panel"><div class="rbac-panel-heading"><span class="rbac-section-icon">♟</span><div><h2>Matriks Role &amp; Permission</h2><p>Daftar role dalam sistem beserta jumlah pengguna dan permission yang ditetapkan.</p></div>@if(auth()->user()?->roleKey() === 'super_admin')<button type="button" onclick="openRoleModal('create')" style="margin-left:auto;height:36px;padding:0 14px;border-radius:9px;border:none;background:#ed172b;color:#fff;font-size:12.5px;font-weight:700;cursor:pointer;">+ Tambah Role Baru</button>@endif</div><form class="rbac-filter-bar" method="get" action="{{ route('admin.roles.index') }}"><label class="rbac-search"><span>⌕</span><input name="role_q" value="{{ $roleSearch }}" placeholder="Cari role..."></label><select name="role_sort"><option value="nama_role" @selected($roleSort==='nama_role')>Urutkan Nama</option><option value="users_count" @selected($roleSort==='users_count')>Urutkan Pengguna</option></select><button class="rbac-apply">⌯ Terapkan</button><a class="rbac-reset" href="{{ route('admin.roles.index') }}">↻ Reset</a></form><div class="rbac-table-wrap"><table class="rbac-table"><thead><tr><th>No</th><th>Role</th><th>Jumlah Pengguna</th><th>Permission Aktif</th><th>Status</th><th>Aksi</th></tr></thead><tbody>@forelse($roles as $index=>$role)<tr><td>{{ $index+1 }}</td><td><strong>{{ ucwords(str_replace('_',' ',$role->nama_role)) }}</strong></td><td>{{ $role->users_count }}</td><td><span class="rbac-count tone-{{ $role->permissions->count()>4?'purple':($role->permissions->count()>2?'blue':'amber') }}">{{ $role->permissions->count() }} permission</span></td><td><span class="rbac-status status-green"><i></i>Terdaftar</span></td><td>@if(auth()->user()?->roleKey() === 'super_admin')<button type="button" data-role-id="{{ $role->id_role }}" data-role-name="{{ $role->nama_role }}" data-role-desc="{{ $role->deskripsi }}" data-role-perms='@json($role->permissions->pluck("id_permission")->values())' data-update-url="{{ route('admin.roles.update', $role) }}" onclick="openRoleModal('edit', this)" style="padding:5px 11px;border-radius:7px;border:1px solid #cbd5e1;background:#fff;color:#0f172a;font-size:12px;font-weight:600;cursor:pointer;">Atur Akses</button>@else<span class="rbac-disabled-action">—</span>@endif</td></tr>@empty<tr><td colspan="6" class="rbac-empty">Belum ada role yang cocok.</td></tr>@endforelse</tbody></table></div><div class="rbac-pagination">{{ $roles->count() }} role ditampilkan dari database</div></section>
            <section class="rbac-panel"><div class="rbac-panel-heading"><span class="rbac-section-icon">▤</span><div><h2>Permission Terdaftar</h2><p>Daftar permission yang tersedia dalam sistem.</p></div></div><form class="rbac-filter-bar" method="get" action="{{ route('admin.roles.index') }}"><label class="rbac-search"><span>⌕</span><input name="permission_q" value="{{ $permissionSearch }}" placeholder="Cari permission..."></label><select disabled><option>Semua Modul</option></select><button class="rbac-apply">⌯ Terapkan</button><a class="rbac-reset" href="{{ route('admin.roles.index') }}">↻ Reset</a></form><div class="rbac-table-wrap"><table class="rbac-table permission-table"><thead><tr><th>Kode</th><th>Nama Permission</th><th>Jumlah Role</th><th>Modul</th><th>Status</th><th>Aksi</th></tr></thead><tbody>@forelse($permissions as $permission)<tr><td><strong>{{ $permission->kode }}</strong></td><td>{{ $permission->nama ?: '—' }}</td><td>{{ $permission->roles_count }}</td><td><span class="rbac-module">{{ ucfirst(strtok($permission->kode,'.')) }}</span></td><td><span class="rbac-status status-green"><i></i>Terdaftar</span></td><td><span class="rbac-disabled-action">—</span></td></tr>@empty<tr><td colspan="6" class="rbac-empty">Belum ada permission yang cocok.</td></tr>@endforelse</tbody></table></div><div class="rbac-pagination">{{ $permissions->count() }} permission terdaftar</div></section>
        </div>
        <aside class="rbac-side-stack"><section class="rbac-side-panel"><div class="rbac-side-heading"><span>▥</span><div><h2>Distribusi Role</h2><p>Persentase pengguna untuk setiap role.</p></div></div>@forelse($roleDistribution->sortByDesc('total') as $row)<div class="rbac-role-row"><strong>{{ $row['name'] }}</strong><small>{{ $row['total'] }} pengguna</small><b>{{ round($row['total']/$distributionTotal*100) }}%</b><span class="rbac-meter"><i style="width:{{ round($row['total']/$distributionTotal*100) }}%"></i></span></div>@empty<div class="rbac-empty">Belum ada distribusi role.</div>@endforelse</section><section class="rbac-side-panel"><div class="rbac-side-heading"><span>◉</span><div><h2>Ringkasan Otorisasi</h2><p>Informasi penting terkait aktivitas RBAC.</p></div></div><div class="rbac-ops-grid">@foreach([['red','♟','Role Terdaftar',$roles->count()],['blue','▤','Permission Digunakan',$permissionUsed],['amber','◷','Review Pending','Belum tersedia'],['purple','♜','Audit Lulus','Belum tersedia']] as [$tone,$icon,$label,$value])<div class="rbac-ops-tile tone-{{ $tone }}"><span>{{ $icon }}</span><div><strong>{{ $value }}</strong><small>{{ $label }}</small></div></div>@endforeach</div><p class="rbac-side-note">Role dan permission bersifat read-only pada dashboard ini. Mutasi RBAC tetap mengikuti workflow serta otorisasi server-side yang sudah tersedia.</p></section></aside>
    </div>
</div>

<style>
#roleModalBackdrop[hidden] { display: none !important; }
</style>
<div id="roleModalBackdrop" hidden onclick="if(event.target===this) closeRoleModal()" style="position:fixed;inset:0;z-index:1050;background:rgba(15,23,42,.45);backdrop-filter:blur(2px);display:flex;align-items:center;justify-content:center;padding:20px;">
    <div style="width:100%;max-width:640px;max-height:90vh;background:#fff;border-radius:16px;border:1px solid #e2e8f0;box-shadow:0 20px 50px rgba(15,23,42,.2);display:flex;flex-direction:column;overflow:hidden;">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f1f5f9;">
            <div>
                <div id="roleModalKicker" style="font-size:11px;font-weight:700;letter-spacing:.08em;color:#ed172b;">SUPER ADMIN · RBAC</div>
                <h3 id="roleModalTitle" style="margin:3px 0 0;font-size:17px;font-weight:800;color:#0f172a;">Tambah Role Baru</h3>
            </div>
            <button type="button" onclick="closeRoleModal()" style="width:30px;height:30px;border-radius:8px;border:1px solid #e2e8f0;background:#fff;color:#64748b;font-size:18px;cursor:pointer;">&times;</button>
        </div>
        <form id="roleModalForm" method="POST" action="{{ route('admin.roles.store') }}" style="display:flex;flex-direction:column;overflow:hidden;flex:1;">
            @csrf
            <input type="hidden" id="roleModalMethod" name="_method" value="POST" disabled>
            <div style="padding:18px 20px;overflow-y:auto;flex:1;display:flex;flex-direction:column;gap:14px;">
                <div id="roleNameFieldWrap" style="display:flex;flex-direction:column;gap:5px;">
                    <label for="roleNameInput" style="font-size:12px;font-weight:700;color:#334155;">Kode Role (huruf kecil &amp; underscore) *</label>
                    <input id="roleNameInput" name="nama_role" placeholder="contoh: car_wash_staff" pattern="[a-zA-Z0-9_]+" style="height:38px;padding:0 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;" required>
                </div>
                <div style="display:flex;flex-direction:column;gap:5px;">
                    <label for="roleDescInput" style="font-size:12px;font-weight:700;color:#334155;">Deskripsi Role</label>
                    <input id="roleDescInput" name="deskripsi" placeholder="Deskripsi singkat peran..." style="height:38px;padding:0 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;">
                </div>
                <div>
                    <div style="font-size:12px;font-weight:700;color:#334155;margin-bottom:8px;">Pilih Hak Akses (Permissions)</div>
                    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;max-height:260px;overflow-y:auto;padding:10px;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;">
                        @foreach($permissions as $perm)
                            <label style="display:flex;align-items:flex-start;gap:8px;padding:7px 9px;border-radius:8px;background:#fff;border:1px solid #e2e8f0;cursor:pointer;font-size:12px;">
                                <input type="checkbox" name="permissions[]" value="{{ $perm->id_permission }}" class="role-perm-checkbox" style="margin-top:2px;">
                                <span><strong style="display:block;color:#0f172a;">{{ $perm->kode }}</strong><small style="color:#64748b;">{{ $perm->nama ?: 'Akses modul' }}</small></span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
            <div style="padding:14px 20px;background:#f8fafc;border-top:1px solid #f1f5f9;display:flex;justify-content:flex-end;gap:8px;">
                <button type="button" onclick="closeRoleModal()" style="height:36px;padding:0 14px;border-radius:8px;border:1px solid #cbd5e1;background:#fff;color:#334155;font-size:12.5px;font-weight:600;cursor:pointer;">Batal</button>
                <button type="submit" style="height:36px;padding:0 16px;border-radius:8px;border:none;background:#ed172b;color:#fff;font-size:12.5px;font-weight:700;cursor:pointer;">Simpan Hak Akses</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRoleModal(mode, btn) {
    const backdrop = document.getElementById('roleModalBackdrop');
    const form = document.getElementById('roleModalForm');
    const methodEl = document.getElementById('roleModalMethod');
    const titleEl = document.getElementById('roleModalTitle');
    const nameWrap = document.getElementById('roleNameFieldWrap');
    const nameInput = document.getElementById('roleNameInput');
    const descInput = document.getElementById('roleDescInput');
    const checkboxes = document.querySelectorAll('.role-perm-checkbox');

    checkboxes.forEach(cb => cb.checked = false);

    if (mode === 'edit' && btn) {
        form.action = btn.dataset.updateUrl;
        methodEl.disabled = false;
        methodEl.value = 'PUT';
        titleEl.textContent = 'Atur Hak Akses — ' + btn.dataset.roleName.replace(/_/g, ' ').toUpperCase();
        nameWrap.style.display = 'none';
        nameInput.required = false;
        descInput.value = btn.dataset.roleDesc || '';
        const selected = JSON.parse(btn.dataset.rolePerms || '[]').map(Number);
        checkboxes.forEach(cb => { cb.checked = selected.includes(Number(cb.value)); });
    } else {
        form.action = @json(route('admin.roles.store'));
        methodEl.disabled = true;
        titleEl.textContent = 'Tambah Role Baru';
        nameWrap.style.display = 'flex';
        nameInput.required = true;
        nameInput.value = '';
        descInput.value = '';
    }
    backdrop.style.display = 'flex';
    backdrop.removeAttribute('hidden');
}

function closeRoleModal() {
    const backdrop = document.getElementById('roleModalBackdrop');
    if (backdrop) {
        backdrop.setAttribute('hidden', '');
        backdrop.style.display = 'none';
    }
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeRoleModal();
});
</script>
@endsection
