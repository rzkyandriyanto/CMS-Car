@extends('layouts.app')
@section('title','Booking Servis · YZA Automotive')
@php
$returnToGarage=request('from')==='garage';$inspectionService=$types->first(fn($t)=>str_contains(strtolower($t->nama_servis),'pengecekan'));$gridTypes=$inspectionService?$types->reject(fn($t)=>$t->getKey()===$inspectionService->getKey()):$types;$initialVehicleId=old('id_kendaraan_pelanggan',$selectedVehicleId ?: $vehicles->first()?->getKey());$initialServiceId=old('id_jenis_servis',$gridTypes->first()?->getKey() ?: $types->first()?->getKey());$initialDate=old('tanggal');$initialTime=old('jam');$initialPromoId=old('id_promo','');
$claimedPromos=$claimedPromos??collect();$unclaimedPromos=$unclaimedPromos??collect();$totalUnclaimedCount=$totalUnclaimedCount??$unclaimedPromos->count();
$formatPromoBenefit=fn($p)=>$p->jenis_benefit==='persentase'?'Diskon '.rtrim(rtrim(number_format((float)$p->nilai_benefit,2,',','.'),'0'),',').'%':($p->jenis_benefit==='nominal'?'Potongan Rp '.number_format((float)$p->nilai_benefit,0,',','.'):'Benefit Member');
$promoPayload=$claimedPromos->concat($unclaimedPromos)->mapWithKeys(fn($p)=>[(string)$p->getKey()=>['id'=>(string)$p->getKey(),'code'=>$p->kode_promo,'name'=>$p->nama,'scope'=>$p->berlaku_untuk,'type'=>$p->jenis_benefit,'value'=>(float)($p->nilai_benefit??0),'max_discount'=>$p->maksimum_benefit!==null&&(float)$p->maksimum_benefit>0?(float)$p->maksimum_benefit:null,'benefit'=>$formatPromoBenefit($p)]]);
$unclaimedPayload=$unclaimedPromos->map(fn($p)=>['id'=>(string)$p->getKey(),'code'=>$p->kode_promo,'name'=>$p->nama,'scope'=>$p->berlaku_untuk,'type'=>$p->jenis_benefit,'value'=>(float)($p->nilai_benefit??0),'max_discount'=>$p->maksimum_benefit!==null&&(float)$p->maksimum_benefit>0?(float)$p->maksimum_benefit:null,'benefit'=>$formatPromoBenefit($p),'points_cost'=>(int)($p->points_cost??0),'claim_url'=>route('pelanggan.promo.claim',$p)])->values();
$vehiclePayload=$vehicles->mapWithKeys(fn($v)=>[(string)$v->getKey()=>['name'=>trim($v->merk.' '.$v->model),'plate'=>$v->nomor_polisi?:'Nomor polisi belum diisi','year'=>$v->tahun?:'Tahun belum diisi','color'=>$v->warna?:'Warna belum diisi','mileage'=>number_format((int)($v->kilometer??0),0,',','.').' km','image'=>$v->booking_image,'verified'=>(bool)$v->is_verified_ownership]]);
$servicePayload=$types->mapWithKeys(fn($t)=>[(string)$t->getKey()=>['name'=>$t->nama_servis,'price'=>((float)$t->estimasi_harga>0)?'Rp '.number_format((float)$t->estimasi_harga,0,',','.'):'Sesuai Hasil Cek','price_amount'=>(float)$t->estimasi_harga,'duration'=>max(1,(int)$t->duration_minutes)]]);
@endphp
@push('styles')
<style>
.member-horizontal-shell .member-main-content:has(.member-schedule-page){padding:0!important;max-width:none!important}.member-schedule-page{width:100%;max-width:none;margin:0;visibility:hidden}.member-schedule-page.is-ready{visibility:visible}
.member-schedule-page{width:min(96vw,1700px);margin:auto;padding:0 0 54px;background:#f4f7fa;color:#111923}.member-schedule-head{position:relative;min-height:190px;display:flex;justify-content:space-between;align-items:flex-end;gap:24px;padding:32px clamp(24px,4vw,64px) 26px;overflow:hidden;background:#070b10 url('/assets/images/middle/service-workshop-neon.png') center/cover;border-bottom:2px solid #f11b35;color:#fff}.member-schedule-head:after{content:'';position:absolute;inset:0;background:linear-gradient(90deg,rgba(6,12,20,.94),rgba(6,12,20,.64) 43%,rgba(6,12,20,.18));pointer-events:none}.member-schedule-head>div,.member-schedule-head>a{position:relative;z-index:1}.member-schedule-head h1{margin:0;color:#fff;font-size:clamp(42px,4.2vw,68px);letter-spacing:-.055em;line-height:1}.member-schedule-head p{margin:10px 0 0;color:#f2f5f7;font-size:15px}.member-schedule-eyebrow{display:block;margin-bottom:9px;color:#ff2441;font-size:11px;font-weight:900;letter-spacing:.2em;text-transform:uppercase}.member-schedule-back{align-self:flex-end;color:#ffccd2;font-size:13px;font-weight:900;text-decoration:none}.schedule-stepper{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin:0 0 18px;padding:8px 12px;border:1px solid #e0e6ec;border-top:0;border-radius:0 0 14px 14px;background:#fff;box-shadow:0 12px 26px #12213012}.schedule-step{display:flex;align-items:center;gap:10px;min-height:48px;padding:0 14px;border:0;border-radius:10px;background:transparent;color:#8794a2;font:inherit;font-size:12px;font-weight:900;text-align:left;cursor:pointer}.schedule-step span{display:grid;place-items:center;width:28px;height:28px;border:1px solid #d4dee7;border-radius:50%;font-size:11px}.schedule-step.is-active{background:#fff0f2;color:#d61b32}.schedule-step.is-active span,.schedule-step.is-complete span{border-color:#e31d35;background:#e31d35;color:#fff}.schedule-step.is-complete{color:#d61b32}.schedule-step.is-complete span{font-size:0}.schedule-step.is-complete span:before{content:'✓';font-size:13px}.schedule-step:disabled{cursor:default}.schedule-layout{display:grid;grid-template-columns:minmax(0,2.25fr) minmax(360px,1fr);gap:20px;align-items:start}.schedule-main,.schedule-summary{min-width:0;border:1px solid #e0e6ec;border-radius:16px;background:#fff;box-shadow:0 14px 32px #13223112}.schedule-main{padding:clamp(22px,2.5vw,34px)}.schedule-summary{position:sticky;top:20px;overflow:hidden}.schedule-panel[hidden],.wizard-collapsed[hidden]{display:none}.schedule-panel h2{margin:0;color:#101720;font-size:clamp(24px,2vw,32px);letter-spacing:-.04em}.schedule-panel-intro{margin:8px 0 24px;color:#718093;font-size:14px}.schedule-field{display:grid;gap:8px;margin-bottom:20px}.schedule-field label{color:#273342;font-size:13px;font-weight:900}.schedule-field select,.schedule-field input,.schedule-field textarea{box-sizing:border-box;width:100%;border:1px solid #d6dfe7;border-radius:10px;background:#fff;color:#16202d;font:inherit;font-size:14px;outline:0}.schedule-field select,.schedule-field input{min-height:48px;padding:0 14px}.schedule-field textarea{min-height:126px;padding:13px 14px;resize:vertical}.schedule-help{margin:0;color:#8794a2;font-size:12px;line-height:1.5}.schedule-error{margin:0 0 18px;padding:13px 16px;border:1px solid #ffc5cc;border-radius:11px;background:#fff1f3;color:#bb1d31;font-size:13px}.schedule-error strong{display:block;margin-bottom:4px}.service-options{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-bottom:25px}.schedule-choice{position:relative;display:flex;flex-direction:column;align-items:flex-start;gap:5px;min-height:132px;padding:16px;border:1px solid #dce3e9;border-radius:13px;background:#fff;color:#172432;text-align:left;cursor:pointer}.schedule-choice.is-selected{border:2px solid #e31d35;background:#fff6f7;box-shadow:0 8px 18px #e31d3518}.schedule-choice.is-selected:after{content:'✓';position:absolute;top:13px;right:13px;display:grid;place-items:center;width:22px;height:22px;border-radius:50%;background:#e31d35;color:#fff;font-size:12px}.schedule-choice:before{content:'✦';display:grid;place-items:center;width:40px;height:40px;border-radius:50%;background:#f1f5f8;color:#142333;font-size:21px}.schedule-choice:nth-child(2):before{content:'ϟ'}.schedule-choice:nth-child(3):before{content:'❄'}.schedule-choice:nth-child(4):before{content:'⚙'}.schedule-choice:nth-child(5):before{content:'◉'}.schedule-choice:nth-child(6):before{content:'⌁'}.schedule-choice.is-selected:before{background:#fbe4e8;color:#e31d35}.schedule-choice-title{position:absolute;top:18px;left:68px;font-size:15px}.schedule-choice-description{min-height:34px;padding-left:52px;color:#6e7c8b;font-size:11px;line-height:1.4}.schedule-choice-meta{display:flex;gap:12px;margin-top:auto;color:#293a4b;font-size:11px;font-weight:850}.schedule-choice-meta b{color:#d91d34}.booking-date-time{display:flex;flex-direction:column;gap:22px;width:100%}.calendar-box,.slot-area{margin:0;padding:24px;border:1px solid #e0e6ec;border-radius:16px;background:#fbfcfd;width:100%;box-sizing:border-box}.calendar-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}.calendar-head h3{margin:0;color:#152130;font-size:17px;text-transform:uppercase;letter-spacing:.07em}.calendar-nav{width:34px;height:34px;border:1px solid #d7e0e8;border-radius:8px;background:#fff;color:#263646;font-size:18px;cursor:pointer}.calendar-nav:disabled{cursor:not-allowed;opacity:.4}.calendar-week,.calendar-grid{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:6px}.calendar-week{margin-bottom:6px;color:#8794a2;font-size:10px;font-weight:900;text-align:center;text-transform:uppercase}.schedule-day{position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;min-height:58px;border:1px solid #e1e7ec;border-radius:9px;background:#fff;color:#1c2a38;font:inherit;cursor:pointer}.schedule-day strong{font-size:14px}.schedule-day small{font-size:8px;font-weight:900;text-transform:uppercase}.schedule-day[data-status=available]{border-color:#a9d9bb;background:#effaf3;color:#176b3d}.schedule-day[data-status=almost_full]{border-color:#edc878;background:#fff8e7;color:#8b5a04}.schedule-day[data-status=full]{border-color:#f1b6c0;background:#fff0f2;color:#ad2639;cursor:not-allowed}.schedule-day[data-status=unavailable],.schedule-day[data-status=past]{border-color:#d9dfe4;background:#f0f2f4;color:#9aa5ae;cursor:not-allowed}.schedule-day[data-status=available]:before,.schedule-day[data-status=almost_full]:before,.schedule-day[data-status=full]:before{content:'';position:absolute;top:6px;right:6px;width:6px;height:6px;border-radius:50%;background:#55ad78}.schedule-day[data-status=almost_full]:before{background:#e7a82d}.schedule-day[data-status=full]:before{background:#dc6574}.schedule-day.is-selected{outline:3px solid #e31d35;outline-offset:-3px}.schedule-day.is-empty{border:0;background:transparent;cursor:default}.schedule-day:disabled{opacity:1}.calendar-legend{display:flex;flex-wrap:wrap;gap:10px 13px;margin-top:13px;color:#6d7a88;font-size:10px}.calendar-legend span{display:inline-flex;align-items:center;gap:5px}.calendar-legend i{width:8px;height:8px;border-radius:50%;background:#55ad78}.calendar-legend .almost i{background:#e7a82d}.calendar-legend .full i{background:#dc6574}.calendar-legend .closed i,.calendar-legend .past i{background:#aab3bc}.availability-message{padding:20px 8px;color:#788695;font-size:13px;text-align:center}.availability-message.error{color:#bd2538}.slot-area h3{margin:0 0 4px;font-size:18px}.slot-area p{margin:0 0 14px;color:#7a8795;font-size:12px}.slot-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}.schedule-slot{position:relative;display:grid;gap:4px;padding:11px 10px;border:1px solid #dce3e9;border-radius:10px;background:#fff;color:#1b2b3b;text-align:left;cursor:pointer}.schedule-slot strong{font-size:14px}.schedule-slot span{color:#72808e;font-size:10px}.schedule-slot[data-status=available]{border-color:#a9d9bb;background:#effaf3}.schedule-slot[data-status=almost_full]{border-color:#edc878;background:#fff8e7}.schedule-slot[data-status=full]{border-color:#f1b6c0;background:#fff0f2;color:#ad2639}.schedule-slot[data-status=unavailable]{border-color:#d9dfe4;background:#f0f2f4;color:#9aa5ae}.schedule-slot.is-selected{outline:3px solid #e31d35;outline-offset:-3px}.schedule-slot.is-selected:after{content:'✓';position:absolute;right:9px;top:9px;color:#e31d35;font-weight:900}.schedule-slot:disabled{cursor:not-allowed;opacity:1}.step-actions{display:flex;justify-content:space-between;gap:12px;margin-top:24px}.schedule-next,.schedule-submit,.schedule-secondary{min-height:46px;padding:0 20px;border-radius:9px;font:inherit;font-size:13px;font-weight:900;cursor:pointer}.schedule-next,.schedule-submit{border:0;background:#e31d35;color:#fff;box-shadow:0 9px 17px #e31d3533}.schedule-next:disabled{border:1px solid #d8dee4;background:#edf0f2;color:#9aa5ae;cursor:not-allowed;box-shadow:none}.schedule-secondary{border:1px solid #d8e0e7;background:#fff;color:#314252}.wizard-collapsed{margin-bottom:20px;padding:16px 18px;border:1px solid #b9dec5;border-radius:13px;background:#f2fbf5}.wizard-collapsed h3{margin:0 0 7px;color:#197345;font-size:12px;letter-spacing:.1em;text-transform:uppercase}.wizard-collapsed p{margin:3px 0;color:#536575;font-size:13px}.wizard-collapsed button{margin-top:8px;border:0;background:transparent;color:#d61b32;font:inherit;font-size:12px;font-weight:900;cursor:pointer}.detail-summary,.confirmation-block{margin-bottom:22px;padding:16px;border:1px solid #e6ebef;border-radius:12px;background:#f8f9fa}.detail-summary,.confirmation-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.detail-summary div,.confirmation-list div{display:grid;gap:4px}.detail-summary span,.confirmation-list span{color:#84919d;font-size:10px;text-transform:uppercase}.detail-summary strong,.confirmation-list strong{font-size:13px;overflow-wrap:anywhere}.confirmation-block{display:block;background:#fff}.confirmation-block h3{margin:0 0 13px;color:#d61b32;font-size:12px;letter-spacing:.1em;text-transform:uppercase}.notice-box{margin-top:16px;padding:14px 16px;border-radius:11px;background:#fff9ed;color:#72531c;font-size:12px;line-height:1.55}.summary-head{padding:22px 24px 16px;border-bottom:1px solid #edf0f3}.summary-head h2{margin:0;font-size:25px;letter-spacing:-.04em}.summary-head p{margin:6px 0 0;color:#7b8997;font-size:13px}.summary-vehicle{display:grid;grid-template-columns:100px minmax(0,1fr);gap:14px;padding:18px 24px;background:linear-gradient(135deg,#fff7f8,#fff)}.summary-vehicle img{width:100px;height:72px;object-fit:cover;border-radius:10px;background:#f5f6f8}.summary-vehicle h3{margin:2px 0 5px;font-size:18px;line-height:1.15}.summary-vehicle p{margin:0;color:#647486;font-size:12px}.ownership-badge{display:inline-flex;align-items:center;gap:5px;margin-top:8px;padding:5px 8px;border-radius:999px;background:#e7f8ef;color:#087b45;font-size:10px;font-weight:900}.summary-facts{display:grid;padding:5px 24px 14px}.summary-fact{display:flex;justify-content:space-between;gap:12px;padding:10px 0;border-bottom:1px solid #edf0f3;font-size:12px}.summary-fact span{color:#7b8997}.summary-fact strong{max-width:64%;color:#1b2632;text-align:right;overflow-wrap:anywhere}.summary-note{margin:0 24px 22px;padding:12px 14px;border-radius:10px;background:#f7f8fa;color:#718093;font-size:11px;line-height:1.5}.personnel-section{margin-top:24px;padding-top:24px;border-top:1px solid #edf0f3}.personnel-heading{display:flex;justify-content:space-between;align-items:center}.personnel-heading h3{margin:0;font-size:17px}.personnel-optional{padding:5px 8px;border-radius:999px;background:#f0f2f4;color:#6d7a88;font-size:10px;font-weight:900}.personnel-description{margin:7px 0 15px;color:#718093;font-size:12px}.personnel-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}.personnel-card{display:flex;flex-direction:column;align-items:flex-start;gap:6px;min-height:140px;padding:13px;border:1px solid #dce3e9;border-radius:12px;background:#fff;color:#172432;text-align:left;cursor:pointer}.personnel-card.is-selected{border-color:#e31d35;background:#fff7f8}.personnel-card:disabled{cursor:not-allowed;background:#f2f4f6;opacity:.75}.personnel-card.is-selected:after{content:'✓ TEKNISI PILIHAN';color:#d61b32;font-size:9px;font-weight:900}.personnel-avatar{display:grid;place-items:center;width:40px;height:40px;border-radius:50%;background:#182736;color:#fff;font-size:13px;font-weight:900;object-fit:cover}.personnel-name{font-size:13px}.personnel-title,.personnel-meta{color:#718093;font-size:10px}.personnel-message{margin-top:10px;padding:13px;border-radius:10px;background:#f7f8fa;color:#718093;font-size:12px}.personnel-message.error{background:#fff1f3;color:#bd2538}
.member-horizontal-shell .member-main-content:has(.member-schedule-page)>.member-schedule-page{width:100%;max-width:none;margin-inline:0}
.schedule-choice:before{display:none}.schedule-choice-icon{display:grid;place-items:center;flex:0 0 48px;width:48px;height:48px;border-radius:50%;background:#f1f5f8;color:#162536}.schedule-choice.is-selected .schedule-choice-icon{background:#fbe4e8;color:#e31d35}.schedule-choice-icon svg{width:27px;height:27px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.service-options{margin-bottom:0}
.service-or-divider{display:flex;align-items:center;gap:14px;margin:16px 0;color:#64748b;font-size:12px;font-weight:800;text-transform:lowercase;letter-spacing:.04em}
.service-or-divider:before,.service-or-divider:after{content:'';flex:1;height:1px;background:#e2e8f0}
.schedule-choice-wide{width:100%;min-height:80px;flex-direction:row;align-items:center;gap:16px;padding:16px 48px 16px 18px}
.schedule-choice-wide .schedule-choice-body{display:flex;flex-direction:column;gap:4px;min-width:0;flex:1}
.schedule-choice-wide .schedule-choice-title{position:static;top:auto;left:auto;font-size:15px;font-weight:800;color:#101720}
.schedule-choice-wide .schedule-choice-description{min-height:0;padding-left:0;font-size:12px;color:#64748b}
.schedule-choice-wide .schedule-choice-meta{margin-top:0;margin-left:auto;flex-direction:column;align-items:flex-end;gap:2px;flex-shrink:0}
@media (max-width:640px){.schedule-choice-wide{flex-wrap:wrap;padding:16px}.schedule-choice-wide .schedule-choice-meta{margin-left:64px;flex-direction:row;gap:12px}}
.booking-date-time{display:flex;flex-direction:column;gap:20px;width:100%;margin-bottom:22px}
.calendar-box,.slot-area{margin:0;padding:24px;border:1px solid #e2e8f0;border-radius:18px;background:#fff;box-shadow:0 4px 18px rgba(15,23,42,.04);width:100%;box-sizing:border-box}
.calendar-head{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:14px}
.calendar-title-wrap{display:grid;gap:3px}
.calendar-eyebrow{display:block;color:#e31d35;font-size:10.5px;font-weight:900;letter-spacing:.14em;text-transform:uppercase}
.calendar-head h3{margin:0;color:#0f172a;font-size:22px;font-weight:800;letter-spacing:-.025em;text-transform:none}
.calendar-controls{display:flex;align-items:center;gap:8px}
.calendar-today-btn{height:38px;padding:0 13px;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;color:#334155;font:inherit;font-size:12px;font-weight:700;cursor:pointer;transition:all .16s ease}
.calendar-today-btn:hover{border-color:#cbd5e1;background:#f1f5f9;color:#0f172a}
.calendar-today-btn[hidden]{display:none}
.calendar-nav{display:grid;place-items:center;width:38px;height:38px;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;color:#1e293b;font-size:19px;font-weight:700;cursor:pointer;transition:all .16s ease}
.calendar-nav:hover:not(:disabled){border-color:#0f172a;background:#0f172a;color:#fff}
.calendar-nav:active:not(:disabled){transform:scale(.95)}
.calendar-nav:disabled{cursor:not-allowed;opacity:.35;background:#f8fafc}
.calendar-hours-bar{display:flex;align-items:center;gap:8px;margin-bottom:18px;padding:9px 13px;border:1px solid #f1f5f9;border-radius:11px;background:#f8fafc;color:#475569;font-size:12px;font-weight:600}
.calendar-hours-bar svg{width:15px;height:15px;flex:0 0 15px;color:#e31d35}
.calendar-week{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:8px;margin-bottom:8px;color:#64748b;font-size:11px;font-weight:800;letter-spacing:.08em;text-align:center;text-transform:uppercase}
.calendar-week span:nth-child(6),.calendar-week span:nth-child(7){color:#94a3b8}
.calendar-grid{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:8px}
.schedule-day{position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;min-height:66px;padding:8px 4px;border:1px solid #e2e8f0;border-radius:12px;background:#fff;color:#0f172a;font:inherit;cursor:pointer;transition:transform .16s ease,box-shadow .16s ease,border-color .16s ease,background .16s ease}
.schedule-day strong{font-size:17px;font-weight:800;line-height:1;font-variant-numeric:tabular-nums}
.schedule-day .day-meta{display:inline-flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;line-height:1;letter-spacing:.01em;text-transform:none}
.schedule-day[data-status=available]{border-color:#bbf7d0;background:#f0fdf4;color:#14532d}
.schedule-day[data-status=available] .day-meta{color:#15803d}
.schedule-day[data-status=available]:hover:not(:disabled){border-color:#16a34a;background:#dcfce7;transform:translateY(-2px);box-shadow:0 8px 16px rgba(22,163,74,.14)}
.schedule-day[data-status=almost_full]{border-color:#fde68a;background:#fffbeb;color:#78350f}
.schedule-day[data-status=almost_full] .day-meta{color:#b45309}
.schedule-day[data-status=almost_full]:hover:not(:disabled){border-color:#d97706;background:#fef3c7;transform:translateY(-2px);box-shadow:0 8px 16px rgba(217,119,6,.14)}
.schedule-day[data-status=full]{border-color:#fecdd3;background:#fff1f2;color:#9f1239;cursor:not-allowed}
.schedule-day[data-status=full] .day-meta{color:#be123c}
.schedule-day[data-status=unavailable]{border-color:#e2e8f0;background:#f8fafc;color:#94a3b8;cursor:not-allowed}
.schedule-day[data-status=unavailable] .day-meta{color:#94a3b8;font-weight:600}
.schedule-day[data-status=past]{border-color:#f1f5f9;background:transparent;color:#cbd5e1;cursor:not-allowed}
.schedule-day[data-status=past] .day-meta{color:#cbd5e1}
.schedule-day[data-status=available]:before,.schedule-day[data-status=almost_full]:before,.schedule-day[data-status=full]:before{display:none}
.schedule-day.is-today:not(.is-selected){box-shadow:inset 0 0 0 1.5px #0f172a}
.schedule-day.is-today:not(.is-selected):after{content:'HARI INI';position:absolute;top:4px;right:5px;font-size:7.5px;font-weight:900;letter-spacing:.04em;color:#0f172a;opacity:.75}
.schedule-day.is-selected{border-color:#e31d35!important;background:#e31d35!important;color:#fff!important;outline:none!important;transform:translateY(-1px);box-shadow:0 10px 22px rgba(227,29,53,.28)}
.schedule-day.is-selected .day-meta{color:rgba(255,255,255,.92)!important}
.schedule-day:active:not(:disabled){transform:scale(.97)}
.schedule-day.is-empty{border:0;background:transparent;cursor:default;min-height:0}
.schedule-day:disabled{opacity:1}
.calendar-legend{display:flex;flex-wrap:wrap;gap:8px;margin-top:16px;padding-top:14px;border-top:1px solid #f1f5f9;font-size:11px;font-weight:700}
.calendar-legend span{display:inline-flex;align-items:center;padding:4px 10px;border-radius:7px;border:1px solid #bbf7d0;background:#f0fdf4;color:#15803d}
.calendar-legend .almost{border-color:#fde68a;background:#fffbeb;color:#b45309}
.calendar-legend .full{border-color:#fecdd3;background:#fff1f2;color:#be123c}
.calendar-legend .closed{border-color:#e2e8f0;background:#f8fafc;color:#64748b}
.calendar-legend .past{border-color:#f1f5f9;background:#fff;color:#94a3b8}
.calendar-legend .picked{border-color:#e31d35;background:#e31d35;color:#fff}
.availability-message{margin-top:12px;padding:12px 14px;border-radius:10px;background:#f8fafc;color:#475569;font-size:13px;font-weight:600;text-align:center}
.availability-message.error{background:#fff1f2;color:#be123c;border:1px solid #fecdd3}
.slot-head{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;margin-bottom:6px}
.slot-eyebrow{display:block;margin-bottom:3px;color:#e31d35;font-size:10.5px;font-weight:900;letter-spacing:.14em;text-transform:uppercase}
.slot-area h3{margin:0;color:#0f172a;font-size:21px;font-weight:800;letter-spacing:-.02em}
.slot-date-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 13px;border-radius:999px;background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;font-size:12px;font-weight:800}
.slot-area p[data-slot-description]{margin:0 0 18px;color:#64748b;font-size:13.5px}
.slot-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
.slot-empty-notice{grid-column:1/-1;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:34px 20px;border:1.5px dashed #cbd5e1;border-radius:14px;background:#f8fafc;text-align:center;color:#64748b}
.slot-empty-icon{display:grid;place-items:center;width:46px;height:46px;margin-bottom:10px;border-radius:12px;background:#fff;border:1px solid #e2e8f0;color:#e31d35;box-shadow:0 4px 10px rgba(15,23,42,.05)}
.slot-empty-icon svg{width:22px;height:22px}
.slot-empty-notice strong{display:block;color:#0f172a;font-size:15px;font-weight:800;margin-bottom:4px}
.slot-empty-notice p{margin:0;max-width:440px;color:#64748b;font-size:13px;line-height:1.5}
.schedule-slot{position:relative;display:flex;flex-direction:column;align-items:flex-start;justify-content:center;gap:6px;min-height:66px;padding:13px 15px;border:1px solid #e2e8f0;border-radius:12px;background:#fff;color:#0f172a;text-align:left;cursor:pointer;transition:transform .16s ease,box-shadow .16s ease,border-color .16s ease,background .16s ease}
.schedule-slot .slot-time-row{display:flex;align-items:baseline;justify-content:space-between;gap:6px;width:100%}
.schedule-slot strong{font-size:14.5px;font-weight:800;color:#0f172a;font-variant-numeric:tabular-nums;white-space:nowrap;letter-spacing:-.01em}
.schedule-slot .slot-tz{font-size:10.5px;font-weight:700;color:#94a3b8;flex-shrink:0}
.schedule-slot .slot-sub{display:inline-flex;align-items:center;gap:4px;font-size:11.5px;font-weight:600;color:#64748b;text-transform:none}
.schedule-slot[data-status=available] .slot-sub{color:#059669}
.schedule-slot[data-status=available]:hover:not(:disabled){border-color:#cbd5e1;background:#f8fafc;transform:translateY(-1px);box-shadow:0 6px 16px rgba(15,23,42,.06)}
.schedule-slot[data-status=almost_full]{border-color:#fde68a;background:#fffdf7}
.schedule-slot[data-status=almost_full] .slot-sub{color:#d97706}
.schedule-slot[data-status=almost_full]:hover:not(:disabled){border-color:#f59e0b;background:#fffbeb;transform:translateY(-1px);box-shadow:0 6px 16px rgba(217,119,6,.08)}
.schedule-slot[data-status=full]{border-color:#f1f5f9;background:#f8fafc;color:#94a3b8;cursor:not-allowed}
.schedule-slot[data-status=full] strong{color:#94a3b8}
.schedule-slot[data-status=full] .slot-sub{color:#e11d48}
.schedule-slot[data-status=unavailable]{border-color:#f1f5f9;background:#f8fafc;color:#94a3b8;cursor:not-allowed}
.schedule-slot[data-status=unavailable] strong,.schedule-slot[data-status=unavailable] .slot-tz{color:#cbd5e1}
.schedule-slot.is-selected{border-color:#e31d35!important;background:#fff5f6!important;box-shadow:0 0 0 1px #e31d35,0 8px 20px rgba(227,29,53,.12)!important}
.schedule-slot.is-selected strong{color:#0f172a!important}
.schedule-slot.is-selected .slot-tz{color:#e31d35!important}
.schedule-slot.is-selected .slot-sub{color:#e31d35!important;font-weight:700}
.schedule-slot:active:not(:disabled){transform:scale(.98)}
.schedule-help{font-size:14px}
.wizard-collapsed{position:relative;display:flex;align-items:center;justify-content:space-between;gap:16px;min-height:0;margin-bottom:20px;padding:14px 18px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;box-shadow:none}.wizard-collapsed:before{display:none}.wizard-collapsed .wizard-collapsed-info{display:grid;gap:2px;min-width:0}.wizard-collapsed h3{margin:0;color:#15803d;font-size:12px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}.wizard-collapsed p{margin:0;color:#334155;font-size:13px;line-height:1.4}.wizard-collapsed p:last-of-type{color:#64748b;font-size:12px}.wizard-collapsed button{position:static;transform:none;flex-shrink:0;margin:0;padding:8px 14px;border:1px solid #cbd5e1;border-radius:8px;background:#fff;color:#0f172a;font-size:12px;font-weight:700;box-shadow:none;animation:none;transition:all .15s ease}.wizard-collapsed button:after{display:none}.wizard-collapsed button:hover,.wizard-collapsed button:focus-visible{border-color:#0f172a;background:#0f172a;color:#fff;box-shadow:none;transform:none}
.detail-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px 16px;margin-bottom:22px;padding:14px 16px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc}.detail-summary div{display:grid;gap:3px}.detail-summary span{color:#64748b;font-size:11px;font-weight:600;text-transform:none}.detail-summary strong{color:#0f172a;font-size:13px;font-weight:700;overflow-wrap:anywhere}
.personnel-section{margin-top:24px;padding-top:20px;border-top:1px solid #f1f5f9}.personnel-heading{display:flex;justify-content:space-between;align-items:center;margin-bottom:4px}.personnel-heading h3{margin:0;color:#0f172a;font-size:15px;font-weight:800}.personnel-optional{padding:3px 9px;border-radius:999px;background:#f1f5f9;color:#64748b;font-size:10px;font-weight:700}.personnel-description{margin:0 0 14px;color:#64748b;font-size:13px}.personnel-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.personnel-card{position:relative;display:flex;flex-direction:column;align-items:flex-start;gap:4px;min-height:0;padding:14px 16px;border:1px solid #e2e8f0;border-radius:12px;background:#fff;color:#0f172a;text-align:left;cursor:pointer;transition:border-color .15s ease,background .15s ease}.personnel-card:hover:not(:disabled){border-color:#cbd5e1;background:#f8fafc}.personnel-card.is-selected{border-color:#e31d35;background:#fff8f9;box-shadow:0 0 0 1px #e31d35}.personnel-card:disabled{cursor:not-allowed;background:#f8fafc;opacity:.6}.personnel-card.is-selected:after{content:'✓ Dipilih';position:absolute;top:14px;right:14px;color:#e31d35;font-size:11px;font-weight:700}.personnel-avatar{display:grid;place-items:center;width:34px;height:34px;margin-bottom:4px;border-radius:50%;background:#f1f5f9;color:#0f172a;font-size:12px;font-weight:800}.personnel-card.is-selected .personnel-avatar{background:#fbe4e8;color:#e31d35}.personnel-name{font-size:13.5px;font-weight:700;color:#0f172a}.personnel-title,.personnel-meta{color:#64748b;font-size:11.5px;line-height:1.35}.personnel-message{margin-top:10px;padding:0;background:transparent;color:#64748b;font-size:12px}
[data-panel="2"] .schedule-field{margin-bottom:16px}[data-panel="2"] .schedule-field textarea{min-height:92px;padding:12px 14px;border-color:#e2e8f0;border-radius:11px;font-size:13.5px;line-height:1.5;transition:border-color .15s ease,box-shadow .15s ease}[data-panel="2"] .schedule-field textarea:focus{border-color:#e31d35;box-shadow:0 0 0 3px rgba(227,29,53,.08)}
.confirmation-block{margin-bottom:14px;padding:20px 22px;border:1px solid #e2e8f0;border-radius:14px;background:#fff}
.confirmation-block h3{margin:0 0 16px;padding-bottom:12px;border-bottom:1px solid #f1f5f9;color:#0f172a;font-size:12.5px;font-weight:800;letter-spacing:.05em;text-transform:uppercase}
.confirmation-list{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px 20px}
.confirmation-list div{display:grid;gap:4px;align-content:start}
.confirmation-list div.is-full{grid-column:1/-1;padding-top:12px;border-top:1px dashed #f1f5f9}
.confirmation-list span{color:#64748b;font-size:11.5px;font-weight:600;text-transform:none}
.confirmation-list strong{color:#0f172a;font-size:13.5px;font-weight:700;line-height:1.4;overflow-wrap:anywhere}
.confirmation-list strong[data-confirm-price]{color:#0f172a;font-weight:700}
.confirmation-list strong[data-confirm-discount]{color:#059669;font-weight:700}
.confirmation-list strong[data-confirm-total]{color:#e31d35;font-weight:800}
.notice-box{display:flex;align-items:flex-start;gap:10px;margin-top:16px;padding:13px 16px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;color:#475569;font-size:12.5px;line-height:1.5}
.notice-box svg{width:16px;height:16px;flex:0 0 16px;margin-top:1px;color:#64748b}
[data-panel="3"] .step-actions{margin-top:22px;padding-top:20px;border-top:1px solid #f1f5f9;justify-content:flex-start}
.schedule-summary{position:sticky;top:92px;border:1px solid #e2e8f0;border-radius:16px;background:#fff;box-shadow:0 12px 30px -10px rgba(15,23,42,.07);overflow:hidden}
.summary-head{padding:18px 20px 14px;border-bottom:1px solid #f1f5f9;background:#fff}
.summary-eyebrow{display:inline-flex;align-items:center;gap:6px;margin-bottom:4px;color:#e31d35;font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase}
.summary-eyebrow::before{content:'';width:6px;height:6px;border-radius:999px;background:#e31d35}
.summary-head h2{margin:0;color:#0f172a;font-size:19px!important;font-weight:800;letter-spacing:-.02em;line-height:1.2}
.summary-head p{margin:4px 0 0;color:#64748b;font-size:12px!important;line-height:1.4}
.summary-vehicle{display:grid;grid-template-columns:88px minmax(0,1fr);gap:12px;align-items:center;margin:14px 20px 6px;padding:11px 12px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc}
.summary-vehicle img{width:88px!important;height:62px!important;object-fit:cover;border-radius:8px;border:1px solid #e2e8f0;background:#fff}
.summary-vehicle h3{margin:0 0 3px;color:#0f172a;font-size:15px!important;font-weight:800;line-height:1.2}
.summary-vehicle p{margin:0;color:#64748b;font-size:11.5px!important;line-height:1.35}
.ownership-badge{display:inline-flex;align-items:center;gap:4px;margin-top:6px;padding:3px 8px;border-radius:999px;background:#ecfdf5;border:1px solid #bbf7d0;color:#059669;font-size:10px;font-weight:700}
.summary-facts{display:grid;padding:6px 20px 8px}
.summary-fact{display:flex;align-items:baseline;justify-content:space-between;gap:12px;padding:8px 0;border-bottom:1px dashed #f1f5f9;font-size:12.5px!important}
.summary-fact:last-child{border-bottom:0}
.summary-fact span{color:#64748b;font-weight:500}
.summary-fact strong{max-width:62%;color:#0f172a;font-weight:700;text-align:right;overflow-wrap:anywhere}
.summary-pricing{margin:6px 20px 14px;padding:13px 15px;border:1px solid #e2e8f0;border-radius:12px;background:#fcfcfd;display:grid;gap:8px}
.summary-price-row{display:flex;align-items:center;justify-content:space-between;gap:10px;font-size:12.5px}
.summary-price-row[hidden]{display:none!important}
.summary-price-row span{color:#64748b;font-weight:600}
.summary-price-row strong{color:#0f172a;font-weight:700;font-variant-numeric:tabular-nums}
.summary-price-row.is-discount span{display:inline-flex;align-items:center;gap:6px;color:#059669}
.summary-price-row.is-discount strong{color:#059669;font-weight:800}
.summary-promo-pill{display:inline-flex;align-items:center;padding:1px 6px;border-radius:5px;background:#ecfdf5;border:1px solid #bbf7d0;color:#047857;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:9.5px;font-weight:700}
.summary-total-row{display:flex;align-items:baseline;justify-content:space-between;gap:10px;padding-top:9px;margin-top:2px;border-top:1px dashed #e2e8f0}
.summary-total-row>span{color:#0f172a;font-size:12.5px;font-weight:800;text-transform:uppercase;letter-spacing:.04em}
.summary-total-val{display:flex;align-items:baseline;gap:7px;text-align:right}
.summary-total-val del{color:#94a3b8;font-size:11.5px;font-weight:600;font-variant-numeric:tabular-nums}
.summary-total-val strong{color:#e31d35;font-size:16px;font-weight:800;letter-spacing:-.02em;font-variant-numeric:tabular-nums}
.summary-actions{padding:0 20px 16px;display:grid;gap:10px}
.summary-submit-btn{width:100%;min-height:46px;display:inline-flex;align-items:center;justify-content:center;gap:8px;border:0;border-radius:11px;background:#e31d35;color:#fff;font:inherit;font-size:13.5px;font-weight:800;cursor:pointer;box-shadow:0 10px 20px -6px rgba(227,29,53,.35);transition:transform .15s ease,background .15s ease,box-shadow .15s ease,opacity .15s ease}
.summary-submit-btn:hover:not(:disabled){background:#be123c;transform:translateY(-1px);box-shadow:0 12px 24px -6px rgba(227,29,53,.42)}
.summary-submit-btn:disabled{background:#e2e8f0;color:#94a3b8;cursor:not-allowed;box-shadow:none;transform:none}
.summary-note{margin:0!important;padding:10px 12px;border-radius:9px;background:#f8fafc;border:1px solid #f1f5f9;color:#64748b;font-size:11.5px!important;line-height:1.45}
.coupon-section{margin-top:24px;padding-top:20px;border-top:1px solid #f1f5f9}
.coupon-group-head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin:16px 0 10px}
.coupon-sublabel{display:inline-flex;align-items:center;gap:7px;margin:0;color:#334155;font-size:11px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
.coupon-sublabel::before{content:'';width:6px;height:6px;border-radius:999px;background:#e31d35}
.coupon-sublabel.is-muted::before{background:#94a3b8}
.coupon-grid,.coupon-unclaimed-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.coupon-card{position:relative;display:flex;flex-direction:column;align-items:stretch;gap:6px;min-height:142px;padding:14px 15px 12px 17px;border:1px solid #e2e8f0;border-radius:14px;background:#fff;color:#0f172a;text-align:left;cursor:pointer;overflow:hidden;transition:transform .16s ease,border-color .16s ease,background .16s ease,box-shadow .16s ease}
.coupon-card::before{content:'';position:absolute;left:0;top:12px;bottom:12px;width:3px;border-radius:0 3px 3px 0;background:#e2e8f0;transition:background .16s ease,top .16s ease,bottom .16s ease,width .16s ease}
.coupon-card:hover:not(:disabled){border-color:#cbd5e1;background:#fcfdfe;transform:translateY(-1px);box-shadow:0 10px 24px -10px rgba(15,23,42,.08)}
.coupon-card:hover:not(:disabled)::before{background:#94a3b8}
.coupon-card.is-selected{border-color:#e31d35;background:linear-gradient(180deg,#fff9fa 0%,#fff 70%);box-shadow:0 0 0 1px #e31d35,0 12px 26px -12px rgba(227,29,53,.22)}
.coupon-card.is-selected::before{top:0;bottom:0;width:4px;border-radius:0;background:#e31d35}
.coupon-card:disabled,.coupon-card.is-disabled{opacity:.46;cursor:not-allowed;background:#f8fafc;border-color:#e2e8f0;transform:none;box-shadow:none}
.coupon-card:disabled:hover,.coupon-card.is-disabled:hover{border-color:#e2e8f0;background:#f8fafc;transform:none;box-shadow:none}
.coupon-card:disabled::before,.coupon-card.is-disabled::before{background:#cbd5e1}
.coupon-card-unclaimed{cursor:default;background:#fcfcfd}
.coupon-card-unclaimed:hover{border-color:#cbd5e1;background:#fff;transform:translateY(-1px);box-shadow:0 10px 24px -10px rgba(15,23,42,.07)}
.coupon-card-top{display:flex;align-items:center;justify-content:space-between;gap:8px;width:100%}
.coupon-code{display:inline-flex;align-items:center;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:10.5px;font-weight:700;letter-spacing:.03em;padding:3px 8px;border-radius:6px;border:1px solid #e2e8f0;background:#f8fafc;color:#334155;transition:all .15s ease}
.coupon-card.is-selected .coupon-code{border-color:#fecdd3;background:#fff1f2;color:#be123c}
.coupon-badge{display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:999px;font-size:10px;font-weight:700;background:#ecfdf5;color:#059669;border:1px solid #bbf7d0;white-space:nowrap}
.coupon-badge:empty{display:none}
.coupon-card.is-selected .coupon-badge{background:#e31d35;border-color:#e31d35;color:#fff}
.coupon-card.is-disabled .coupon-badge{background:#f1f5f9;border-color:#e2e8f0;color:#64748b}
.coupon-badge-points{background:#f8fafc;border-color:#e2e8f0;color:#475569}
.coupon-benefit{font-size:15px;font-weight:800;letter-spacing:-.02em;color:#0f172a;line-height:1.25;margin-top:2px}
.coupon-name{font-size:12px;color:#64748b;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.coupon-card-foot{margin-top:auto;padding-top:10px;border-top:1px dashed #e2e8f0;display:flex;align-items:center;justify-content:space-between;gap:8px;width:100%}
.coupon-card.is-selected .coupon-card-foot{border-top-color:#fecdd3}
.coupon-cta-text{font-size:11px;font-weight:700;color:#64748b;transition:color .15s ease}
.coupon-card:hover:not(:disabled) .coupon-cta-text{color:#0f172a}
.coupon-card.is-selected .coupon-cta-text{color:#e31d35}
.coupon-card.is-disabled .coupon-cta-text{color:#94a3b8;font-weight:600}
.coupon-radio{width:16px;height:16px;border-radius:999px;border:1.5px solid #cbd5e1;background:#fff;display:inline-grid;place-items:center;flex-shrink:0;transition:all .15s ease}
.coupon-radio::after{content:'';width:6px;height:6px;border-radius:999px;background:#fff;transform:scale(0);transition:transform .15s ease}
.coupon-card.is-selected .coupon-radio{border-color:#e31d35;background:#e31d35}
.coupon-card.is-selected .coupon-radio::after{transform:scale(1)}
.coupon-claim-btn{width:100%;display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:7px 12px;border:1px solid #0f172a;border-radius:8px;background:#0f172a;color:#fff;font:inherit;font-size:11.5px;font-weight:700;cursor:pointer;transition:all .15s ease}
.coupon-claim-btn:hover:not(:disabled){border-color:#e31d35;background:#e31d35;color:#fff;box-shadow:0 4px 12px rgba(227,29,53,.22)}
.coupon-claim-btn:disabled{opacity:.6;cursor:wait}
.coupon-all-wrap{margin-top:14px;padding-top:12px;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
.coupon-all-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border:1px solid #e2e8f0;border-radius:9px;background:#f8fafc;color:#0f172a;font-size:12px;font-weight:700;text-decoration:none;transition:all .15s ease}
.coupon-all-btn:hover{border-color:#0f172a;background:#0f172a;color:#fff}
.coupon-empty{margin:0;color:#64748b;font-size:12px}
.coupon-feedback{margin-top:10px;padding:9px 12px;border-radius:9px;background:#f0fdf4;border:1px solid #bbf7d0;font-size:12px;font-weight:600;color:#15803d}.coupon-feedback.error{background:#fff1f2;border-color:#fecdd3;color:#be123c}
@media(max-width:900px){.confirmation-list,.coupon-grid,.coupon-unclaimed-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:640px){.coupon-grid,.coupon-unclaimed-grid{grid-template-columns:1fr}}
.schedule-modal-overlay{position:fixed;inset:0;z-index:1200;display:grid;place-items:center;padding:20px;background:rgba(15,23,42,.56);backdrop-filter:blur(3px)}.schedule-modal-overlay[hidden]{display:none}
.schedule-modal{width:min(760px,100%);max-height:90vh;display:flex;flex-direction:column;border:1px solid #e2e8f0;border-radius:18px;background:#fff;box-shadow:0 24px 60px rgba(15,23,42,.24);overflow:hidden}
.schedule-modal-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:18px 24px;border-bottom:1px solid #f1f5f9}.schedule-modal-head h3{margin:0;color:#0f172a;font-size:18px;font-weight:800}.schedule-modal-head p{margin:2px 0 0;color:#64748b;font-size:12.5px}
.schedule-modal-close{display:grid;place-items:center;width:34px;height:34px;border:1px solid #e2e8f0;border-radius:9px;background:#fff;color:#475569;font-size:20px;line-height:1;cursor:pointer}.schedule-modal-close:hover{border-color:#0f172a;color:#0f172a}
.schedule-modal-body{padding:20px 24px;overflow-y:auto;display:grid;gap:18px}
.schedule-modal-slots{padding-top:16px;border-top:1px solid #f1f5f9}.schedule-modal-slots-head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:12px}.schedule-modal-slots-head h4{margin:0;color:#0f172a;font-size:15px;font-weight:800}
.schedule-modal-foot{display:flex;align-items:center;justify-content:flex-end;gap:10px;padding:14px 24px;border-top:1px solid #f1f5f9;background:#f8fafc}
.schedule-modal-foot .schedule-next:disabled{opacity:.45;cursor:not-allowed}
.confirm-modal{width:min(540px,100%)}
.confirm-modal-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px 16px;padding:14px 16px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc}
.confirm-modal-grid div{display:grid;gap:3px}
.confirm-modal-grid div.is-full{grid-column:1/-1}
.confirm-modal-grid span{color:#64748b;font-size:11px;font-weight:600}
.confirm-modal-grid strong{color:#0f172a;font-size:13px;font-weight:700;line-height:1.35}
.confirm-modal-pricing{margin:0!important}
.confirm-modal-note{display:flex;align-items:flex-start;gap:8px;padding:10px 12px;border-radius:10px;background:#f8fafc;border:1px solid #f1f5f9;color:#64748b;font-size:12px;line-height:1.45}
.confirm-modal-note svg{width:15px;height:15px;flex:0 0 15px;margin-top:2px;color:#e31d35}
@media(max-width:640px){.schedule-modal-head,.schedule-modal-body,.schedule-modal-foot{padding:14px 16px}.schedule-modal .slot-grid,.confirm-modal-grid{grid-template-columns:1fr}}
.schedule-field>.schedule-help{margin-top:-2px}
.member-schedule-head h1{font-size:clamp(54px,5vw,84px);font-weight:950}.member-schedule-head p{font-size:17px;font-weight:700}.member-schedule-eyebrow{font-size:13px}.member-schedule-back{font-size:15px}.schedule-step{font-size:14px}.schedule-step span{width:34px;height:34px;font-size:12px}.schedule-panel h2{font-size:clamp(30px,2.6vw,42px)}.schedule-panel-intro{font-size:16px}.schedule-field label{font-size:14px}.schedule-field select,.schedule-field input,.schedule-field textarea{font-size:16px}.schedule-choice{min-height:154px;padding:18px}.schedule-choice:before{width:48px;height:48px;font-size:25px}.schedule-choice-title{top:22px;left:78px;font-size:18px}.schedule-choice-description{padding-left:60px;font-size:13px}.schedule-choice-meta{font-size:13px}.summary-head h2{font-size:30px}.summary-head p{font-size:15px}.summary-vehicle{grid-template-columns:120px minmax(0,1fr);gap:16px;padding:20px 24px}.summary-vehicle img{width:120px;height:84px}.summary-vehicle h3{font-size:21px}.summary-vehicle p{font-size:14px}.summary-fact{font-size:13px}.summary-note{font-size:13px}
@media(max-width:1100px){.member-schedule-page{width:94vw}.schedule-layout{grid-template-columns:minmax(0,1.55fr) minmax(310px,1fr)}.slot-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.personnel-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:760px){.member-schedule-page{width:100%;padding-inline:10px}.member-schedule-head{min-height:220px;margin-inline:-10px;padding:28px 20px 24px;align-items:flex-start;flex-direction:column}.member-schedule-head h1{font-size:42px}.member-schedule-back{margin-top:auto}.schedule-stepper{grid-template-columns:1fr;margin-inline:-10px}.schedule-layout{grid-template-columns:1fr}.schedule-summary{position:static;grid-row:1}.schedule-main{grid-row:2;padding:17px}.service-options{grid-template-columns:1fr}.calendar-box,.slot-area{padding:16px 14px}.calendar-head h3{font-size:18px}.calendar-grid,.calendar-week{gap:5px}.schedule-day{min-height:54px;padding:6px 2px;border-radius:10px}.schedule-day strong{font-size:14.5px}.schedule-day .day-meta{font-size:8.5px}.schedule-day.is-today:not(.is-selected):after{display:none}.slot-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}.detail-summary,.confirmation-list,.personnel-grid{grid-template-columns:1fr}.step-actions{flex-direction:column-reverse}.step-actions>*{width:100%}}
@media(max-width:760px){.wizard-collapsed{min-height:0;padding:18px 18px 20px}.wizard-collapsed button{position:static;transform:none;margin-top:13px}.wizard-collapsed button:hover,.wizard-collapsed button:focus-visible{transform:translateY(-2px)}}
@media(prefers-reduced-motion:reduce){.wizard-collapsed button{animation:none;transition:none}}
</style>
@endpush
@section('content')
<div class="member-schedule-page" data-schedule-app data-current-month="{{ now()->format('Y-m') }}" data-initial-vehicle="{{ $initialVehicleId }}" data-initial-service="{{ $initialServiceId }}" data-initial-date="{{ $initialDate }}" data-initial-time="{{ $initialTime }}">
<header class="member-schedule-head"><div><span class="member-schedule-eyebrow">YZA SERVICE</span><h1>Booking Servis</h1><p>Pilih jadwal kedatangan yang tersedia untuk kendaraan Anda.</p></div><a class="member-schedule-back" href="{{ $returnToGarage?route('pelanggan.kendaraan.index'):route('pelanggan.booking-servis.index') }}">← {{ $returnToGarage?'Kembali ke Kendaraan Saya':'Lihat Booking Saya' }}</a></header>
<nav class="schedule-stepper" aria-label="Tahapan booking servis"><button type="button" class="schedule-step is-active" data-step-button="1" aria-current="step"><span>01</span> Pilih Jadwal</button><button type="button" class="schedule-step" data-step-button="2" disabled><span>02</span> Detail Servis</button><button type="button" class="schedule-step" data-step-button="3" disabled><span>03</span> Konfirmasi</button></nav>
@if($errors->any())<div class="schedule-error" role="alert"><strong>Periksa kembali detail booking Anda.</strong><span>{{ $errors->first() }}</span></div>@endif
<div class="schedule-layout"><main class="schedule-main"><form id="booking-schedule-form" method="post" action="{{ route('pelanggan.booking-servis.store') }}" data-schedule-form>@csrf
<input type="hidden" name="id_kendaraan_pelanggan" value="{{ $initialVehicleId }}" data-vehicle-input><input type="hidden" name="id_jenis_servis" value="{{ $initialServiceId }}" data-service-input><input type="hidden" name="tanggal" value="{{ $initialDate }}" data-date-input><input type="hidden" name="jam" value="{{ $initialTime }}" data-time-input><input type="hidden" name="id_preferred_petugas" value="" data-preferred-technician-input><input type="hidden" name="id_promo" value="{{ $initialPromoId }}" data-promo-input>
<section class="schedule-panel" data-panel="1">
<h2>01 Pilih Jadwal</h2>
<p class="schedule-panel-intro">Pilih kendaraan, jenis servis, tanggal, dan waktu kedatangan yang tersedia.</p>
@if($vehicles->isEmpty())
<div class="schedule-error" role="alert"><strong>Belum ada kendaraan aktif.</strong><span>Tambahkan atau aktifkan kendaraan di halaman Kendaraan Saya sebelum mengajukan booking servis.</span></div>
@endif
@if($types->isEmpty())
<div class="schedule-error" role="alert"><strong>Jenis servis belum tersedia.</strong><span>Saat ini belum ada layanan servis aktif yang dapat dipesan. Silakan hubungi tim YZA Automotive.</span></div>
@endif
<div class="schedule-field"><label for="booking-vehicle">Kendaraan yang akan diservis</label><select id="booking-vehicle" required data-vehicle-select><option value="">Pilih kendaraan</option>@foreach($vehicles as $v)<option value="{{ $v->getKey() }}" @selected((string)$initialVehicleId===(string)$v->getKey())>{{ trim($v->merk.' '.$v->model) }} · {{ $v->nomor_polisi?:'Tanpa nomor polisi' }}</option>@endforeach</select><p class="schedule-help">Hanya kendaraan aktif milik akun Anda yang dapat dipilih.</p></div>
<div class="schedule-field"><label>Jenis Servis</label><div class="service-options" data-service-options>@foreach($gridTypes as $t)<button type="button" class="schedule-choice {{ (string)$initialServiceId===(string)$t->getKey()?'is-selected':'' }}" data-service-choice="{{ $t->getKey() }}"><strong class="schedule-choice-title">{{ $t->nama_servis }}</strong><span class="schedule-choice-description">{{ $t->deskripsi?:'Perawatan kendaraan oleh tim YZA Automotive.' }}</span><span class="schedule-choice-meta"><b>Rp {{ number_format((float)$t->estimasi_harga,0,',','.') }}</b><span>± {{ max(1,(int)$t->duration_minutes) }} menit</span></span></button>@endforeach</div>@if($inspectionService)<div class="service-or-divider"><span>atau</span></div><button type="button" class="schedule-choice schedule-choice-wide {{ (string)$initialServiceId===(string)$inspectionService->getKey()?'is-selected':'' }}" data-service-choice="{{ $inspectionService->getKey() }}"><span class="schedule-choice-body"><strong class="schedule-choice-title">Lakukan Pengecekan</strong><span class="schedule-choice-description">{{ $inspectionService->deskripsi?:'Pemeriksaan menyeluruh kondisi kendaraan terlebih dahulu sebelum menentukan tindakan servis.' }}</span></span><span class="schedule-choice-meta"><b>{{ ((float)$inspectionService->estimasi_harga>0)?'Rp '.number_format((float)$inspectionService->estimasi_harga,0,',','.'):'Sesuai Hasil Cek' }}</b><span>± {{ max(1,(int)$inspectionService->duration_minutes) }} menit</span></span></button>@endif</div>
<div class="booking-date-time">
  <section class="calendar-box" aria-labelledby="calendar-title">
    <div class="calendar-head">
      <div class="calendar-title-wrap">
        <span class="calendar-eyebrow">Tanggal Kedatangan</span>
        <h3 id="calendar-title" data-month-label>{{ ucfirst(now()->translatedFormat('F Y')) }}</h3>
      </div>
      <div class="calendar-controls">
        <button type="button" class="calendar-today-btn" data-month-today hidden>Bulan Ini</button>
        <button type="button" class="calendar-nav" data-month-prev aria-label="Bulan sebelumnya">‹</button>
        <button type="button" class="calendar-nav" data-month-next aria-label="Bulan berikutnya">›</button>
      </div>
    </div>
    <div class="calendar-hours-bar">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      <span>Jam Operasional Bengkel: <strong>Senin – Jumat</strong> · 08:00 – 17:00 WIB (Istirahat 12:00 – 13:00 WIB)</span>
    </div>
    <div class="calendar-week" aria-hidden="true"><span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span></div>
    <div class="calendar-grid" data-calendar-grid></div>
    <div class="calendar-legend">
      <span>Tersedia</span>
      <span class="almost">Hampir Penuh</span>
      <span class="full">Penuh</span>
      <span class="closed">Tutup</span>
      <span class="past">Lewat</span>
      <span class="picked">Dipilih</span>
    </div>
    <div class="availability-message" data-calendar-status hidden></div>
  </section>

  <section class="slot-area" aria-labelledby="slot-title" data-slot-area>
    <div class="slot-head">
      <div>
        <span class="slot-eyebrow">Jam Kedatangan</span>
        <h3 id="slot-title">Pilih Jam Kedatangan</h3>
      </div>
      <span class="slot-date-badge" data-slot-date-badge style="display:none;"></span>
    </div>
    <p data-slot-description>Pilih tanggal yang tersedia pada kalender di atas untuk menampilkan jam kedatangan bengkel.</p>
    <div class="slot-grid" data-slot-grid>
      <div class="slot-empty-notice">
        <span class="slot-empty-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </span>
        <strong>Belum Ada Tanggal Dipilih</strong>
        <p>Klik salah satu tanggal hijau (Tersedia) pada kalender di atas untuk melihat pilihan jam kedatangan.</p>
      </div>
    </div>
    <div class="availability-message" data-slot-status hidden></div>
  </section>
</div>
<p class="schedule-help" data-step-one-help>Pilih jenis servis, tanggal, dan waktu kedatangan terlebih dahulu.</p>
<div class="step-actions"><span></span><button type="button" class="schedule-next" data-next-step="2" disabled>Lanjut ke Detail Servis →</button></div>
</section>
<div class="wizard-collapsed" data-collapsed-schedule hidden><div class="wizard-collapsed-info"><h3>✓ Jadwal Dipilih</h3><p data-collapsed-schedule-main></p><p data-collapsed-schedule-meta></p></div><button type="button" data-edit-schedule>Ubah Jadwal</button></div>
<section class="schedule-panel" data-panel="2" hidden><h2>02 Detail Servis</h2><p class="schedule-panel-intro">Lengkapi keluhan atau pilih teknisi bila diperlukan.</p><div class="detail-summary"><div><span>Kendaraan</span><strong data-detail-vehicle>Belum dipilih</strong></div><div><span>Layanan</span><strong data-detail-service>Belum dipilih</strong></div><div><span>Tanggal</span><strong data-detail-date>Belum dipilih</strong></div><div><span>Waktu</span><strong data-detail-time>Belum dipilih</strong></div><div><span>Kilometer</span><strong data-mileage-value>Belum dipilih</strong></div></div><div class="schedule-field"><label for="booking-complaint">Keluhan Kendaraan <span style="font-weight:500;color:#94a3b8">(Opsional)</span></label><textarea id="booking-complaint" name="keluhan" maxlength="1000" placeholder="Contoh: Mesin terasa bergetar saat idle, bunyi saat pengereman, atau AC kurang dingin.">{{ old('keluhan') }}</textarea></div><div class="schedule-field"><label for="booking-note">Catatan Tambahan <span style="font-weight:500;color:#94a3b8">(Opsional)</span></label><textarea id="booking-note" name="catatan" placeholder="Contoh: Mohon hubungi saya sebelum melakukan pekerjaan tambahan.">{{ old('catatan') }}</textarea></div><section class="personnel-section"><div class="personnel-heading"><h3>Preferensi Teknisi</h3><span class="personnel-optional">Opsional</span></div><p class="personnel-description">Pilih teknisi yang Anda inginkan atau biarkan sistem menentukan sesuai ketersediaan.</p><div class="personnel-grid" data-personnel-grid></div><div class="personnel-message" data-personnel-message>Lengkapi jadwal untuk melihat teknisi.</div></section><div class="step-actions"><button type="button" class="schedule-secondary" data-prev-step="1">← Kembali</button><button type="button" class="schedule-next" data-next-step="3">Lanjut ke Konfirmasi →</button></div></section>
<div class="wizard-collapsed" data-collapsed-detail hidden><div class="wizard-collapsed-info"><h3>✓ Detail Servis</h3><p>Keluhan: <span data-collapsed-complaint>-</span></p><p>Catatan: <span data-collapsed-note>-</span></p></div><button type="button" data-edit-detail>Ubah Detail</button></div>
<section class="schedule-panel" data-panel="3" hidden><h2>03 Konfirmasi Booking</h2><p class="schedule-panel-intro">Periksa kembali seluruh ringkasan sebelum mengajukan booking servis.</p><div class="confirmation-block"><h3>Kendaraan &amp; Jadwal</h3><div class="confirmation-list"><div><span>Kendaraan</span><strong data-confirm-vehicle>Belum dipilih</strong></div><div><span>No. Polisi</span><strong data-confirm-plate>Belum dipilih</strong></div><div><span>Tanggal</span><strong data-confirm-date>Belum dipilih</strong></div><div><span>Waktu Kedatangan</span><strong data-confirm-time>Belum dipilih</strong></div><div><span>Estimasi Durasi</span><strong data-confirm-duration>Belum dipilih</strong></div><div><span>Estimasi Selesai</span><strong data-confirm-finish>Belum dipilih</strong></div></div></div><div class="confirmation-block"><h3>Servis &amp; Catatan</h3><div class="confirmation-list"><div><span>Jenis Servis</span><strong data-confirm-service>Belum dipilih</strong></div><div><span>Biaya Servis</span><strong data-confirm-price>Belum dipilih</strong></div><div><span>Potongan Promo</span><strong data-confirm-discount>Tidak Ada</strong></div><div><span>Total Harga</span><strong data-confirm-total>Belum dipilih</strong></div><div><span>Teknisi Pilihan</span><strong data-confirm-technician>Tidak Ada Preferensi</strong></div><div><span>Kupon Promo</span><strong data-confirm-promo>Tidak Pakai Kupon</strong></div><div class="is-full"><span>Keluhan Kendaraan</span><strong data-confirm-complaint>Belum diisi</strong></div><div class="is-full"><span>Catatan Tambahan</span><strong data-confirm-note>Belum diisi</strong></div></div></div>
<section class="confirmation-block coupon-section" style="margin-top:0;padding:20px 22px;border:1px solid #e2e8f0;" aria-labelledby="coupon-title">
  <div class="personnel-heading">
    <h3 id="coupon-title" style="margin:0;padding:0;border:0;">Kupon Promo</h3>
    <span class="personnel-optional">Opsional</span>
  </div>
  <p class="personnel-description" style="margin-top:4px;">Pilih kupon yang sudah diklaim atau klaim kupon yang tersedia untuk booking ini.</p>
  <div class="coupon-group-head">
    <p class="coupon-sublabel">Kupon Sudah Terklaim</p>
  </div>
  <div class="coupon-grid" data-claimed-coupon-grid>
    <button type="button" class="coupon-card {{ !$initialPromoId?'is-selected':'' }}" data-promo-choice="">
      <div class="coupon-card-top"><span class="coupon-code">TANPA KUPON</span><span class="coupon-badge" data-promo-badge>{{ !$initialPromoId?'✓ Dipilih':'' }}</span></div>
      <strong class="coupon-benefit">Tidak Pakai Kupon</strong>
      <span class="coupon-name">Lanjutkan booking dengan tarif standar bengkel.</span>
      <div class="coupon-card-foot"><span class="coupon-cta-text" data-promo-cta>{{ !$initialPromoId?'Tarif standar aktif':'Gunakan tarif standar' }}</span><span class="coupon-radio" aria-hidden="true"></span></div>
    </button>
    @foreach($claimedPromos as $promo)
      @php $isPicked=(string)$initialPromoId===(string)$promo->getKey(); @endphp
      <button type="button" class="coupon-card {{ $isPicked?'is-selected':'' }}" data-promo-choice="{{ $promo->getKey() }}">
        <div class="coupon-card-top"><span class="coupon-code">{{ $promo->kode_promo }}</span><span class="coupon-badge" data-promo-badge>{{ $isPicked?'✓ Dipilih':'Terklaim' }}</span></div>
        <strong class="coupon-benefit">{{ $formatPromoBenefit($promo) }}</strong>
        <span class="coupon-name">{{ $promo->nama }}</span>
        <div class="coupon-card-foot"><span class="coupon-cta-text" data-promo-cta>{{ $isPicked?'Kupon digunakan':'Pakai kupon ini' }}</span><span class="coupon-radio" aria-hidden="true"></span></div>
      </button>
    @endforeach
  </div>
  @if($claimedPromos->isEmpty())
    <p class="coupon-empty" style="margin-top:8px;" data-no-claimed-note>Belum ada kupon yang diklaim. Anda dapat klaim kupon di bawah ini.</p>
  @endif
  @if($unclaimedPromos->isNotEmpty())
    <div data-unclaimed-section>
      <div class="coupon-group-head" style="margin-top:20px;">
        <p class="coupon-sublabel is-muted">Kupon Belum Terklaim</p>
      </div>
      <div class="coupon-unclaimed-grid" data-unclaimed-coupon-grid>
        @foreach($unclaimedPromos->take(3) as $promo)
          <div class="coupon-card coupon-card-unclaimed" data-unclaimed-card="{{ $promo->getKey() }}">
            <div class="coupon-card-top"><span class="coupon-code">{{ $promo->kode_promo }}</span><span class="coupon-badge coupon-badge-points">{{ (int)$promo->points_cost>0?number_format((int)$promo->points_cost,0,',','.').' Poin':'Gratis' }}</span></div>
            <strong class="coupon-benefit">{{ $formatPromoBenefit($promo) }}</strong>
            <span class="coupon-name">{{ $promo->nama }}</span>
            <div class="coupon-card-foot">
              <button type="button" class="coupon-claim-btn" data-claim-promo="{{ $promo->getKey() }}" data-claim-url="{{ route('pelanggan.promo.claim', $promo) }}">Klaim &amp; Pakai</button>
            </div>
          </div>
        @endforeach
      </div>
      <div class="coupon-all-wrap">
        <span class="coupon-empty" data-unclaimed-summary>{{ $totalUnclaimedCount>3?'Menampilkan 3 dari '.$totalUnclaimedCount.' kupon yang belum diklaim.':'Jelajahi semua promo dan kupon member.' }}</span>
        <a class="coupon-all-btn" href="{{ route('pelanggan.promo.index') }}" target="_blank" rel="noopener">Lihat Semua Kupon →</a>
      </div>
    </div>
  @else
    <div class="coupon-all-wrap">
      <span></span>
      <a class="coupon-all-btn" href="{{ route('pelanggan.promo.index') }}" target="_blank" rel="noopener">Lihat Semua Kupon →</a>
    </div>
  @endif
  <div class="coupon-feedback" data-coupon-feedback hidden></div>
</section>
<div class="notice-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg><span>Biaya servis belum termasuk sparepart, material, atau pekerjaan tambahan yang ditemukan setelah pemeriksaan teknisi.</span></div><div class="step-actions"><button type="button" class="schedule-secondary" data-prev-step="2">← Kembali</button></div></section>
</form></main>
<aside class="schedule-summary" aria-live="polite">
  <div class="summary-head">
    <span class="summary-eyebrow">YZA Booking Summary</span>
    <h2>Ringkasan Booking</h2>
    <p>Detail jadwal &amp; rincian biaya mengikuti pilihan Anda.</p>
  </div>
  <div class="summary-vehicle">
    <img src="{{ asset('assets/images/hero/yza-hero-reference-v3.png') }}" alt="" data-summary-image>
    <div>
      <h3 data-summary-name>Belum memilih kendaraan</h3>
      <p data-summary-meta>Pilih kendaraan untuk melihat detailnya.</p>
      <span class="ownership-badge" data-summary-ownership hidden>✓ Verified YZA Ownership</span>
    </div>
  </div>
  <div class="summary-facts">
    <div class="summary-fact"><span>Jenis Servis</span><strong data-summary-service>Belum dipilih</strong></div>
    <div class="summary-fact"><span>Tanggal</span><strong data-summary-date>Belum dipilih</strong></div>
    <div class="summary-fact"><span>Waktu Kedatangan</span><strong data-summary-time>Belum dipilih</strong></div>
    <div class="summary-fact"><span>Estimasi Durasi</span><strong data-summary-duration>Belum dipilih</strong></div>
    <div class="summary-fact"><span>Estimasi Selesai</span><strong data-summary-finish>Belum dipilih</strong></div>
    <div class="summary-fact"><span>Teknisi Pilihan</span><strong data-summary-technician>Tidak Ada Preferensi</strong></div>
    <div class="summary-fact"><span>Kupon Promo</span><strong data-summary-promo>Tidak Pakai Kupon</strong></div>
  </div>
  <div class="summary-pricing">
    <div class="summary-price-row">
      <span>Biaya Servis</span>
      <strong data-summary-price>Belum dipilih</strong>
    </div>
    <div class="summary-price-row is-discount" data-summary-discount-row hidden>
      <span>Potongan Promo <em class="summary-promo-pill" data-summary-discount-code></em></span>
      <strong data-summary-discount>- Rp 0</strong>
    </div>
    <div class="summary-total-row">
      <span>Total Harga</span>
      <div class="summary-total-val">
        <del data-summary-original-price hidden></del>
        <strong data-summary-total>Belum dipilih</strong>
      </div>
    </div>
  </div>
  <div class="summary-actions">
    <button type="button" class="schedule-submit summary-submit-btn" data-summary-submit disabled>Ajukan Booking Servis →</button>
    <p class="summary-note">Jadwal akan diproses oleh tim YZA setelah booking dikirim. Slot akan divalidasi kembali saat pengajuan.</p>
  </div>
</aside>
</div>
<div class="schedule-modal-overlay" data-schedule-modal hidden role="dialog" aria-modal="true" aria-labelledby="schedule-modal-title">
  <div class="schedule-modal">
    <div class="schedule-modal-head">
      <div>
        <h3 id="schedule-modal-title">Ubah Jadwal Kedatangan</h3>
        <p data-modal-service-label>Pilih tanggal dan jam baru yang tersedia.</p>
      </div>
      <button type="button" class="schedule-modal-close" data-schedule-modal-close aria-label="Tutup modal">&times;</button>
    </div>
    <div class="schedule-modal-body">
      <div>
        <div class="calendar-head">
          <div class="calendar-title-wrap">
            <span class="calendar-eyebrow">Tanggal Kedatangan</span>
            <h3 data-modal-month-label>{{ ucfirst(now()->translatedFormat('F Y')) }}</h3>
          </div>
          <div class="calendar-controls">
            <button type="button" class="calendar-today-btn" data-modal-month-today hidden>Bulan Ini</button>
            <button type="button" class="calendar-nav" data-modal-month-prev aria-label="Bulan sebelumnya">‹</button>
            <button type="button" class="calendar-nav" data-modal-month-next aria-label="Bulan berikutnya">›</button>
          </div>
        </div>
        <div class="calendar-week" aria-hidden="true"><span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span></div>
        <div class="calendar-grid" data-modal-calendar-grid></div>
        <div class="calendar-legend">
          <span>Tersedia</span>
          <span class="almost">Hampir Penuh</span>
          <span class="full">Penuh</span>
          <span class="closed">Tutup</span>
          <span class="past">Lewat</span>
          <span class="picked">Dipilih</span>
        </div>
        <div class="availability-message" data-modal-calendar-status hidden></div>
      </div>
      <div class="schedule-modal-slots">
        <div class="schedule-modal-slots-head">
          <h4>Pilih Jam Kedatangan</h4>
          <span class="slot-date-badge" data-modal-slot-date style="display:none;"></span>
        </div>
        <div class="slot-grid" data-modal-slot-grid></div>
        <div class="availability-message" data-modal-slot-status hidden></div>
      </div>
    </div>
    <div class="schedule-modal-foot">
      <button type="button" class="schedule-secondary" data-schedule-modal-close>Batal</button>
      <button type="button" class="schedule-next" data-schedule-modal-save disabled>Simpan Jadwal</button>
    </div>
  </div>
</div>
<div class="schedule-modal-overlay" data-confirm-modal hidden role="dialog" aria-modal="true" aria-labelledby="confirm-modal-title">
  <div class="schedule-modal confirm-modal">
    <div class="schedule-modal-head">
      <div>
        <span class="summary-eyebrow" style="margin-bottom:2px;">Langkah Konfirmasi</span>
        <h3 id="confirm-modal-title">Ajukan Booking Servis?</h3>
        <p>Periksa kembali detail booking Anda sebelum dikirim ke tim bengkel.</p>
      </div>
      <button type="button" class="schedule-modal-close" data-confirm-modal-close aria-label="Tutup modal">&times;</button>
    </div>
    <div class="schedule-modal-body" style="gap:14px;">
      <div class="confirm-modal-grid">
        <div class="is-full"><span>Kendaraan</span><strong data-modal-confirm-vehicle>Belum dipilih</strong></div>
        <div><span>Jenis Servis</span><strong data-modal-confirm-service>Belum dipilih</strong></div>
        <div><span>Teknisi Pilihan</span><strong data-modal-confirm-technician>Tidak Ada Preferensi</strong></div>
        <div class="is-full"><span>Jadwal Kedatangan</span><strong data-modal-confirm-schedule>Belum dipilih</strong></div>
        <div class="is-full"><span>Kupon Promo</span><strong data-modal-confirm-promo>Tidak Pakai Kupon</strong></div>
      </div>
      <div class="summary-pricing confirm-modal-pricing">
        <div class="summary-price-row">
          <span>Biaya Servis</span>
          <strong data-modal-confirm-price>Belum dipilih</strong>
        </div>
        <div class="summary-price-row is-discount" data-modal-confirm-discount-row hidden>
          <span>Potongan Promo <em class="summary-promo-pill" data-modal-confirm-discount-code></em></span>
          <strong data-modal-confirm-discount>- Rp 0</strong>
        </div>
        <div class="summary-total-row">
          <span>Total Harga</span>
          <div class="summary-total-val">
            <del data-modal-confirm-original hidden></del>
            <strong data-modal-confirm-total>Belum dipilih</strong>
          </div>
        </div>
      </div>
      <div class="confirm-modal-note">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        <span>Dengan menekan <strong>Ya, Ajukan Booking</strong>, permintaan servis dan slot waktu Anda akan langsung diproses oleh YZA Automotive.</span>
      </div>
    </div>
    <div class="schedule-modal-foot">
      <button type="button" class="schedule-secondary" data-confirm-modal-close>Periksa Kembali</button>
      <button type="button" class="schedule-submit" data-confirm-modal-submit>Ya, Ajukan Booking →</button>
    </div>
  </div>
</div>
</div>
@endsection
@push('scripts')
<script>
(()=>{const root=document.querySelector('[data-schedule-app]');if(!root)return;const $=s=>root.querySelector(s),$$=s=>[...root.querySelectorAll(s)],vehicles=@json($vehiclePayload),services=@json($servicePayload),promos=@json($promoPayload),availabilityUrl=@json(route('pelanggan.booking-servis.availability')),technicianUrl=@json(route('pelanggan.booking-servis.technicians'));const _now=new Date(),todayStr=`${_now.getFullYear()}-${String(_now.getMonth()+1).padStart(2,'0')}-${String(_now.getDate()).padStart(2,'0')}`;let month=root.dataset.currentMonth,selectedDate=root.dataset.initialDate||'',selectedTime=root.dataset.initialTime||'',selectedService=root.dataset.initialService||'',selectedPromo=@json((string)$initialPromoId),step=1,monthDays={},daySlots=[],selectedTechnician=null,monthRequest=0,dayRequest=0,techRequest=0,techController=null,userPickedDay=false;const vehicleSelect=$('[data-vehicle-select]'),vehicleInput=$('[data-vehicle-input]'),serviceInput=$('[data-service-input]'),dateInput=$('[data-date-input]'),timeInput=$('[data-time-input]'),techInput=$('[data-preferred-technician-input]'),promoInput=$('[data-promo-input]'),vehicle=()=>vehicles[vehicleSelect?.value]||null,service=()=>services[selectedService]||null,promo=()=>promos[selectedPromo]||null,setText=(s,v)=>{const n=$(s);if(n)n.textContent=v||'Belum dipilih'},formatDate=v=>{if(!v)return'Belum dipilih';const[y,m,d]=v.split('-');return new Date(+y,+m-1,+d).toLocaleDateString('id-ID',{weekday:'long',day:'2-digit',month:'long',year:'numeric'})},monthLabel=v=>{const[y,m]=v.split('-');const l=new Date(+y,+m-1,1).toLocaleDateString('id-ID',{month:'long',year:'numeric'});return l.charAt(0).toUpperCase()+l.slice(1)},duration=m=>{m=+m||0;return m>=60?`± ${Math.floor(m/60)} jam${m%60?` ${m%60} menit`:''}`:`± ${m} menit`},finish=()=>{const t=service();if(!selectedTime||!t)return'';const[h,m]=selectedTime.split(':').map(Number),d=new Date(2000,0,1,h,m);d.setMinutes(d.getMinutes()+t.duration);return d.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit',hour12:false})};
const progressKey=`yza.member.booking.progress:${location.pathname}${location.search}`;const navType=performance.getEntriesByType?.('navigation')?.[0]?.type||'navigate';const hasServerErrors=@json($errors->any());if(navType!=='reload'&&!hasServerErrors){try{sessionStorage.removeItem(progressKey)}catch(_){}}let savedProgress=null;try{savedProgress=JSON.parse(sessionStorage.getItem(progressKey)||'null')}catch(_){}if(savedProgress){if(savedProgress.vehicle&&vehicles[savedProgress.vehicle]){vehicleSelect.value=savedProgress.vehicle;vehicleInput.value=savedProgress.vehicle}if(savedProgress.service&&services[savedProgress.service]){selectedService=savedProgress.service;serviceInput.value=savedProgress.service}if(savedProgress.promo&&promos[savedProgress.promo]){selectedPromo=savedProgress.promo;if(promoInput)promoInput.value=selectedPromo}if(savedProgress.date&&savedProgress.date>=todayStr){selectedDate=savedProgress.date;if(selectedDate.slice(0,7)>=root.dataset.currentMonth)month=selectedDate.slice(0,7);if(savedProgress.time)selectedTime=savedProgress.time}if($('#booking-complaint'))$('#booking-complaint').value=savedProgress.complaint||'';if($('#booking-note'))$('#booking-note').value=savedProgress.note||''}
const formatRp=n=>'Rp '+Math.round(n).toLocaleString('id-ID'),calcPricing=(t,pr)=>{if(!t)return{base:'Belum dipilih',hasPromo:false,code:'',disc:'Tidak Ada',total:'Belum dipilih',strike:''};const base=Number(t.price_amount||0),baseLabel=base>0?formatRp(base):(t.price||'Sesuai Hasil Cek');if(!pr)return{base:baseLabel,hasPromo:false,code:'',disc:'Tidak Ada',total:baseLabel,strike:''};if(base<=0)return{base:baseLabel,hasPromo:true,code:pr.code,disc:pr.benefit,total:`${baseLabel} (${pr.benefit})`,strike:''};let d=pr.type==='persentase'?Math.floor(base*(Number(pr.value||0)/100)):(pr.type==='nominal'?Math.round(Number(pr.value||0)):0);if(pr.max_discount)d=Math.min(d,Math.round(Number(pr.max_discount)));d=Math.max(0,Math.min(base,d));const finalTotal=Math.max(0,base-d);return{base:baseLabel,hasPromo:true,code:pr.code,disc:d>0?`- ${formatRp(d)}`:pr.benefit,total:formatRp(finalTotal),strike:d>0?baseLabel:''}};
const updateSummary=()=>{const v=vehicle(),t=service(),pr=promo(),promoLabel=pr?`${pr.code} · ${pr.benefit}`:'Tidak Pakai Kupon',f=finish(),pInfo=calcPricing(t,pr),img=$('[data-summary-image]');if(img)img.src=v?.image||'{{ asset('assets/images/hero/yza-hero-reference-v3.png') }}';setText('[data-summary-name]',v?.name||'Belum memilih kendaraan');setText('[data-summary-meta]',v?`${v.plate} · ${v.year} · ${v.color}`:'Pilih kendaraan untuk melihat detailnya.');if($('[data-summary-ownership]'))$('[data-summary-ownership]').hidden=!v?.verified;setText('[data-summary-service]',t?.name);setText('[data-summary-date]',formatDate(selectedDate));setText('[data-summary-time]',selectedTime?`${selectedTime} WIB`:'Belum dipilih');setText('[data-summary-duration]',t?duration(t.duration):'Belum dipilih');setText('[data-summary-finish]',f?`${f} WIB`:'Belum dipilih');setText('[data-summary-price]',pInfo.base);const discRow=$('[data-summary-discount-row]'),discCode=$('[data-summary-discount-code]'),origEl=$('[data-summary-original-price]');if(discRow)discRow.hidden=!pInfo.hasPromo;if(discCode)discCode.textContent=pInfo.code;setText('[data-summary-discount]',pInfo.disc);if(origEl){origEl.hidden=!pInfo.strike;origEl.textContent=pInfo.strike}setText('[data-summary-total]',pInfo.total);setText('[data-summary-technician]',selectedTechnician?.display_name||'Tidak Ada Preferensi');setText('[data-summary-promo]',promoLabel);setText('[data-detail-vehicle]',v?.name);setText('[data-detail-service]',t?.name);setText('[data-detail-date]',formatDate(selectedDate));setText('[data-detail-time]',selectedTime?`${selectedTime} WIB`:'Belum dipilih');if($('[data-detail-mileage]'))$('[data-detail-mileage]').value=v?.mileage||'Belum dipilih';setText('[data-confirm-vehicle]',v?.name);setText('[data-confirm-plate]',v?.plate);setText('[data-confirm-date]',formatDate(selectedDate));setText('[data-confirm-time]',selectedTime?`${selectedTime} WIB`:'Belum dipilih');setText('[data-confirm-duration]',t?duration(t.duration):'Belum dipilih');setText('[data-confirm-finish]',f?`${f} WIB`:'Belum dipilih');setText('[data-confirm-service]',t?.name);setText('[data-confirm-price]',pInfo.base);setText('[data-confirm-discount]',pInfo.disc);setText('[data-confirm-total]',pInfo.total);setText('[data-confirm-technician]',selectedTechnician?.display_name||'Tidak Ada Preferensi');setText('[data-confirm-promo]',promoLabel);setText('[data-confirm-complaint]',$('#booking-complaint')?.value||'Belum diisi');setText('[data-confirm-note]',$('#booking-note')?.value||'Belum diisi')};
const setStatus=(s,m,e=false)=>{const n=$(s);if(!n)return;n.hidden=!m;n.classList.toggle('error',e);n.innerHTML=m||''},valid=()=>Boolean(vehicleSelect?.value&&selectedService&&selectedDate&&selectedTime&&daySlots.some(x=>x.time===selectedTime&&x.available)),gate=()=>{const ok=valid(),b=$('[data-next-step="2"]'),sub=$('[data-summary-submit]');if(b)b.disabled=!ok;if(sub)sub.disabled=!ok;setText('[data-step-one-help]',ok?'Jadwal siap. Lanjutkan untuk mengisi detail servis.':'Pilih jenis servis, tanggal, dan waktu kedatangan terlebih dahulu.')};
const serviceIcons={
 'ganti oli':'<svg viewBox="0 0 32 32" aria-hidden="true"><path d="M7 12h15v11H7z"/><path d="M10 12V9h9v3M22 15h3v5h-3M12 17h5M10 27h12"/></svg>',
 'kelistrikan':'<svg viewBox="0 0 32 32" aria-hidden="true"><path d="m18 3-9 14h7l-2 12 9-15h-7z"/></svg>',
 'servis ac':'<svg viewBox="0 0 32 32" aria-hidden="true"><path d="M16 4v24M4 16h24M7.5 7.5l17 17M24.5 7.5l-17 17M16 4l2 3M16 4l-2 3M28 16l-3 2M28 16l-3-2M16 28l2-3M16 28l-2-3M4 16l3 2M4 16l3-2"/></svg>',
 'servis berkala':'<svg viewBox="0 0 32 32" aria-hidden="true"><path d="M19.5 5.5a8 8 0 0 0-9.8 9.8L5 20l7 7 4.7-4.7a8 8 0 0 0 9.8-9.8l-4.2 4.2-3.8-1-1-3.8z"/><path d="m20 20 7 7"/></svg>',
 'servis rem':'<svg viewBox="0 0 32 32" aria-hidden="true"><circle cx="16" cy="16" r="10"/><circle cx="16" cy="16" r="4"/><path d="M16 6v4M16 22v4M6 16h4M22 16h4"/></svg>',
 'tune up':'<svg viewBox="0 0 32 32" aria-hidden="true"><path d="M5 14h4l2-4h10l2 4h4v10H5z"/><path d="M10 14h12M11 24v3M21 24v3M13 18h6"/></svg>',
 'lakukan pengecekan':'<svg viewBox="0 0 32 32" aria-hidden="true"><circle cx="14" cy="14" r="8"/><path d="m20 20 7 7"/><path d="M11 14h6M14 11v6"/></svg>'
};
$$('[data-service-choice]').forEach(card=>{const key=card.querySelector('.schedule-choice-title')?.textContent.trim().toLowerCase(),svg=serviceIcons[key];if(svg){const icon=document.createElement('span');icon.className='schedule-choice-icon';icon.innerHTML=svg;card.prepend(icon)}});
const syncMileage=()=>setText('[data-mileage-value]',vehicle()?.mileage||'Belum dipilih');syncMileage();vehicleSelect?.addEventListener('change',syncMileage);
$$('[data-service-choice]').forEach(card=>card.classList.toggle('is-selected',card.dataset.serviceChoice===selectedService));
const persistProgress=()=>{try{sessionStorage.setItem(progressKey,JSON.stringify({step,vehicle:vehicleSelect?.value||'',service:selectedService,promo:selectedPromo,date:selectedDate,time:selectedTime,complaint:$('#booking-complaint')?.value||'',note:$('#booking-note')?.value||'',technician:selectedTechnician?.id||null}))}catch(_){} };root.addEventListener('input',persistProgress);root.addEventListener('change',persistProgress);root.addEventListener('click',event=>{if(event.target.closest('[data-service-choice],[data-promo-choice],[data-next-step],[data-prev-step],[data-edit-schedule],[data-edit-detail],[data-slot-grid]'))setTimeout(persistProgress,0)});
const scopeLabels={cuci_mobil:'Khusus Car Wash',penjualan_sparepart:'Khusus Spare Part',trade_in:'Khusus Trade-In',penjualan_mobil:'Khusus Mobil'};
const promoEligibility=pr=>{if(!pr)return{ok:true,reason:''};if(pr.scope!=='servis'&&pr.scope!=='semua')return{ok:false,reason:scopeLabels[pr.scope]||'Tidak sesuai layanan'};const svc=(service()?.name||'').toLowerCase(),text=`${pr.code||''} ${pr.name||''}`.toLowerCase();if(/tune\s*up|yza-tuneup/.test(text)&&!svc.includes('tune up'))return{ok:false,reason:'Khusus Tune Up'};if(/kampas\s*rem|yza-brakes/.test(text)&&!svc.includes('rem'))return{ok:false,reason:'Khusus Servis Rem'};if(/evaporator\s*ac|yza-accare/.test(text)&&!svc.includes('ac'))return{ok:false,reason:'Khusus Servis AC'};if(/spooring|yza-spooring/.test(text)&&!svc.includes('spooring')&&!svc.includes('kaki'))return{ok:false,reason:'Khusus Spooring'};if(/coating|yza-coat30/.test(text)&&!svc.includes('coating')&&!svc.includes('body'))return{ok:false,reason:'Khusus Coating'};if(/dyno|yza-dynotest/.test(text)&&!svc.includes('tune up')&&!svc.includes('kelistrikan'))return{ok:false,reason:'Khusus Dyno / ECU'};return{ok:true,reason:'Terklaim'}};
const selectPromo=id=>{const next=String(id||'');if(next&&!promoEligibility(promos[next]).ok)return;selectedPromo=next;if(promoInput)promoInput.value=selectedPromo;$$('[data-promo-choice]').forEach(c=>{const cid=c.dataset.promoChoice;const active=cid===selectedPromo;c.classList.toggle('is-selected',active);const badge=c.querySelector('[data-promo-badge]'),cta=c.querySelector('[data-promo-cta]');if(!cid){if(badge)badge.textContent=active?'✓ Dipilih':'';if(cta)cta.textContent=active?'Tarif standar aktif':'Gunakan tarif standar';return}const elig=promoEligibility(promos[cid]);if(badge)badge.textContent=active?'✓ Dipilih':(elig.ok?'Terklaim':elig.reason);if(cta)cta.textContent=active?'Kupon digunakan':(elig.ok?'Pakai kupon ini':'Tidak berlaku di servis ini')});updateSummary();persistProgress()};
const syncPromoEligibility=()=>{if(selectedPromo&&!promoEligibility(promos[selectedPromo]).ok)selectedPromo='';$$('[data-promo-choice]').forEach(c=>{const cid=c.dataset.promoChoice;if(!cid)return;const elig=promoEligibility(promos[cid]);c.disabled=!elig.ok;c.classList.toggle('is-disabled',!elig.ok)});selectPromo(selectedPromo)};
$('[data-claimed-coupon-grid]')?.addEventListener('click',e=>{const btn=e.target.closest('[data-promo-choice]');if(btn&&!btn.disabled)selectPromo(btn.dataset.promoChoice)});
let unclaimedPool=@json($unclaimedPayload);
const shuffleArr=arr=>{const a=[...arr];for(let i=a.length-1;i>0;i--){const j=Math.floor(Math.random()*(i+1));[a[i],a[j]]=[a[j],a[i]]}return a};
const isSpecificMatch=(pr,svcName)=>{const t=`${pr.code||''} ${pr.name||''}`.toLowerCase();return (/tune\s*up|yza-tuneup/.test(t)&&svcName.includes('tune up'))||(/kampas\s*rem|yza-brakes/.test(t)&&svcName.includes('rem'))||(/evaporator\s*ac|yza-accare/.test(t)&&svcName.includes('ac'))||(/spooring|yza-spooring/.test(t)&&(svcName.includes('spooring')||svcName.includes('kaki')))||(/coating|yza-coat30/.test(t)&&(svcName.includes('coating')||svcName.includes('body')))||(/dyno|yza-dynotest/.test(t)&&(svcName.includes('tune up')||svcName.includes('kelistrikan')))};
const renderUnclaimedCoupons=()=>{const g=$('[data-unclaimed-coupon-grid]'),sec=$('[data-unclaimed-section]');if(!g||!sec)return;if(!unclaimedPool.length){sec.hidden=true;return}sec.hidden=false;const svc=(service()?.name||'').toLowerCase(),specific=[],general=[],other=[];unclaimedPool.forEach(p=>{const elig=promoEligibility(p);if(elig.ok){if(isSpecificMatch(p,svc))specific.push(p);else general.push(p)}else{other.push(p)}});const relevant=[...specific,...general];const picked=[...relevant,...shuffleArr(other)].slice(0,3);g.innerHTML=picked.map(p=>`<div class="coupon-card coupon-card-unclaimed" data-unclaimed-card="${p.id}"><div class="coupon-card-top"><span class="coupon-code">${p.code}</span><span class="coupon-badge coupon-badge-points">${p.points_cost>0?`${Number(p.points_cost).toLocaleString('id-ID')} Poin`:'Gratis'}</span></div><strong class="coupon-benefit">${p.benefit}</strong><span class="coupon-name">${p.name}</span><div class="coupon-card-foot"><button type="button" class="coupon-claim-btn" data-claim-promo="${p.id}" data-claim-url="${p.claim_url}">Klaim &amp; Pakai</button></div></div>`).join('');const sum=$('[data-unclaimed-summary]');if(sum){sum.textContent=relevant.length?`Menampilkan ${picked.length} kupon relevan (${unclaimedPool.length} belum diklaim).`:`Belum ada kupon khusus servis ini, menampilkan ${picked.length} kupon acak.`}};
syncPromoEligibility();renderUnclaimedCoupons();
$('[data-unclaimed-coupon-grid]')?.addEventListener('click',async e=>{const btn=e.target.closest('[data-claim-promo]');if(!btn)return;const id=btn.dataset.claimPromo,url=btn.dataset.claimUrl,fb=$('[data-coupon-feedback]'),csrf=document.querySelector('input[name="_token"]')?.value||'';btn.disabled=true;btn.textContent='Mengklaim...';if(fb){fb.hidden=true;fb.classList.remove('error')}try{const res=await fetch(url,{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});const data=await res.json();if(!res.ok||data.success===false)throw new Error(data.message||'Gagal mengklaim kupon.');const p=promos[id],claimedGrid=$('[data-claimed-coupon-grid]');if(p&&claimedGrid&&!$(`[data-promo-choice="${id}"]`)){const card=document.createElement('button');card.type='button';card.className='coupon-card';card.dataset.promoChoice=id;card.innerHTML=`<div class="coupon-card-top"><span class="coupon-code">${p.code}</span><span class="coupon-badge" data-promo-badge>Terklaim</span></div><strong class="coupon-benefit">${p.benefit}</strong><span class="coupon-name">${p.name}</span><div class="coupon-card-foot"><span class="coupon-cta-text" data-promo-cta>Pakai kupon ini</span><span class="coupon-radio" aria-hidden="true"></span></div>`;claimedGrid.append(card)}$('[data-no-claimed-note]')?.remove();unclaimedPool=unclaimedPool.filter(x=>x.id!==id);renderUnclaimedCoupons();syncPromoEligibility();if(promoEligibility(p).ok)selectPromo(id);if(fb){fb.textContent=data.message||'Kupon berhasil diklaim.';fb.hidden=false}}catch(err){btn.disabled=false;btn.textContent='Klaim & Pakai';if(fb){fb.textContent=err.message||'Gagal mengklaim kupon.';fb.classList.add('error');fb.hidden=false}}});
const restoreStep=()=>{if(!savedProgress?.step||savedProgress.step<2){root.classList.add('is-ready');return}let attempts=0;const timer=setInterval(()=>{attempts++;if(savedProgress.time&&!selectedTime&&daySlots.length){selectedTime=savedProgress.time;timeInput.value=selectedTime;renderSlots();updateSummary();gate()}if(valid()){clearInterval(timer);setStep(Math.min(3,+savedProgress.step));persistProgress();root.classList.add('is-ready')}else if(attempts>60){clearInterval(timer);root.classList.add('is-ready')}},100)};restoreStep();
const clockSvg='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>';
const alertSvg='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
const resetSlotGrid=(msg='Klik salah satu tanggal hijau (Tersedia) pada kalender di atas untuk melihat pilihan jam kedatangan.')=>{const g=$('[data-slot-grid]');if(!g)return;g.innerHTML=`<div class="slot-empty-notice"><span class="slot-empty-icon" aria-hidden="true">${clockSvg}</span><strong>Belum Ada Tanggal Dipilih</strong><p>${msg}</p></div>`;const badge=$('[data-slot-date-badge]');if(badge){badge.style.display='none';badge.textContent=''};setText('[data-slot-description]','Pilih tanggal yang tersedia pada kalender di atas untuk menampilkan jam kedatangan bengkel.');setStatus('[data-slot-status]','')};
const renderCalendar=()=>{const g=$('[data-calendar-grid]');if(!g)return;g.innerHTML='';g.style.gridTemplateColumns='';const[y,m]=month.split('-').map(Number),first=(new Date(y,m-1,1).getDay()+6)%7,total=new Date(y,m,0).getDate();for(let i=0;i<first;i++){const e=document.createElement('span');e.className='schedule-day is-empty';g.append(e)}for(let d=1;d<=total;d++){const value=`${y}-${String(m).padStart(2,'0')}-${String(d).padStart(2,'0')}`;let data=monthDays[value];if(!data){if(value<todayStr){data={status:'past'}}else if(!selectedService){data={status:'unavailable'}}else{const dow=new Date(y,m-1,d).getDay();data=(dow===0||dow===6)?{status:'unavailable'}:{status:'available'}}}const isToday=value===todayStr,b=document.createElement('button');b.type='button';b.className=`schedule-day${selectedDate===value?' is-selected':''}${isToday?' is-today':''}`;b.dataset.status=data.status;b.disabled=!selectedService||!['available','almost_full'].includes(data.status);b.setAttribute('aria-disabled',b.disabled?'true':'false');const label=data.status==='available'?'Tersedia':data.status==='almost_full'?(data.available_slots?`Sisa ${data.available_slots}`:'Terbatas'):data.status==='full'?'Penuh':data.status==='past'?'Lewat':'Tutup';b.innerHTML=`<strong>${d}</strong><span class="day-meta">${label}</span>`;if(!b.disabled)b.addEventListener('click',()=>{userPickedDay=true;loadDay(value,false)});g.append(b)}setText('[data-month-label]',monthLabel(month));if($('[data-month-prev]'))$('[data-month-prev]').disabled=month<=root.dataset.currentMonth;if($('[data-month-today]'))$('[data-month-today]').hidden=month===root.dataset.currentMonth};
const loadMonth=async()=>{if(!selectedService){monthDays={};renderCalendar();setStatus('[data-calendar-status]','Pilih jenis servis terlebih dahulu untuk melihat ketersediaan tanggal.',true);return}const r=++monthRequest;setStatus('[data-calendar-status]','Memuat ketersediaan jadwal bulan ini…');try{const res=await fetch(`${availabilityUrl}?month=${encodeURIComponent(month)}&jenis_servis_id=${selectedService}`,{headers:{Accept:'application/json'}});if(!res.ok)throw Error();const data=await res.json();if(r!==monthRequest)return;monthDays=Object.fromEntries((data.days||[]).map(x=>[x.date,x]));setStatus('[data-calendar-status]','');renderCalendar();if(selectedDate&&monthDays[selectedDate]){if(!['available','almost_full'].includes(monthDays[selectedDate].status)){selectedDate='';selectedTime='';daySlots=[];dateInput.value='';timeInput.value='';resetSlotGrid('Tanggal sebelumnya tidak tersedia. Silakan pilih tanggal lain di kalender.');updateSummary();gate()}else if(!userPickedDay&&!daySlots.length){loadDay(selectedDate,true)}}}catch(_){if(r!==monthRequest)return;setStatus('[data-calendar-status]','Ketersediaan jadwal belum dapat dimuat dari server. Silakan muat ulang halaman.',true);renderCalendar()}};
const renderSlots=()=>{const g=$('[data-slot-grid]');if(!g)return;g.innerHTML='';if(!daySlots.length){g.innerHTML=`<div class="slot-empty-notice" style="border-color:#fecdd3;background:#fff1f2;"><span class="slot-empty-icon" style="color:#be123c;border-color:#fecdd3;" aria-hidden="true">${alertSvg}</span><strong style="color:#9f1239;">Tidak Ada Jam Tersedia</strong><p style="color:#be123c;">Bengkel tidak memiliki slot operasional pada tanggal ini (atau melewati jam operasional hari ini). Silakan pilih tanggal lain di kalender atas.</p></div>`;setStatus('[data-slot-status]','');gate();return}setStatus('[data-slot-status]','');const t=service();daySlots.forEach(x=>{const b=document.createElement('button');b.type='button';b.className=`schedule-slot ${selectedTime===x.time?'is-selected':''}`;b.dataset.status=x.status;b.disabled=!x.available;b.setAttribute('aria-disabled',b.disabled?'true':'false');let endText='';if(t&&x.available){const[sh,sm]=x.time.split(':').map(Number),sd=new Date(2000,0,1,sh,sm+t.duration);endText=` – ${String(sd.getHours()).padStart(2,'0')}:${String(sd.getMinutes()).padStart(2,'0')}`}const sub=selectedTime===x.time?'✓ Jam dipilih':x.status==='available'?`Sisa ${x.capacity_remaining} slot`:x.status==='almost_full'?`Hampir penuh · Sisa ${x.capacity_remaining}`:x.status==='full'?'Penuh':'Tidak tersedia';b.innerHTML=`<span class="slot-time-row"><strong>${x.time}${endText}</strong><span class="slot-tz">WIB</span></span><span class="slot-sub">${sub}</span>`;if(x.available)b.addEventListener('click',()=>{selectedTime=x.time;timeInput.value=x.time;renderSlots();updateSummary();gate();loadTechnicians()});g.append(b)})};
const loadDay=async(date,preserveTime=false)=>{if(!selectedService){return}const r=++dayRequest;selectedDate=date;if(!preserveTime){selectedTime='';timeInput.value=''}dateInput.value=date;daySlots=[];renderCalendar();const badge=$('[data-slot-date-badge]');if(badge){badge.textContent=formatDate(date);badge.style.display='inline-flex'}setText('[data-slot-description]',`Menampilkan pilihan jam kedatangan untuk ${formatDate(date)}:`);setStatus('[data-slot-status]','Memuat slot jam kedatangan…');updateSummary();gate();try{const res=await fetch(`${availabilityUrl}?date=${date}&jenis_servis_id=${selectedService}`,{headers:{Accept:'application/json'}});if(!res.ok)throw Error();const data=await res.json();if(r!==dayRequest)return;daySlots=data.slots||[];if(preserveTime&&selectedTime&&!daySlots.some(x=>x.time===selectedTime&&x.available)){selectedTime='';timeInput.value=''}renderSlots();updateSummary();gate();if(selectedTime)loadTechnicians()}catch(_){if(r!==dayRequest)return;daySlots=[];const g=$('[data-slot-grid]');if(g)g.innerHTML=`<div class="slot-empty-notice" style="border-color:#fecdd3;background:#fff1f2;"><span class="slot-empty-icon" style="color:#be123c;border-color:#fecdd3;" aria-hidden="true">${alertSvg}</span><strong style="color:#9f1239;">Slot Jam Gagal Dimuat</strong><p style="color:#be123c;">Terjadi kendala saat memeriksa ketersediaan jam. Pastikan jenis servis telah dipilih dan coba klik tanggal kembali.</p></div>`;setStatus('[data-slot-status]','Slot belum dapat dimuat.',true);gate()}};
const techInitials=n=>(n||'YZA').split(/\s+/).map(x=>x[0]).slice(0,2).join('').toUpperCase(),techLabels={available:'Tersedia',assigned:'Sedang Bertugas',time_off:'Tidak Bertugas',unavailable:'Tidak Tersedia'};const renderTech=(rows,message='Pilih teknisi atau lanjutkan tanpa preferensi.')=>{const g=$('[data-personnel-grid]');g.innerHTML='';const none=document.createElement('button');none.type='button';none.className=`personnel-card ${!selectedTechnician?'is-selected':''}`;none.innerHTML='<span class="personnel-avatar">—</span><strong class="personnel-name">Tidak Ada Preferensi</strong><span class="personnel-title">YZA menentukan teknisi sesuai ketersediaan.</span>';none.onclick=()=>{selectedTechnician=null;techInput.value='';renderTech(rows);updateSummary()};g.append(none);rows.forEach(x=>{const b=document.createElement('button');b.type='button';b.className=`personnel-card ${selectedTechnician?.id===x.id?'is-selected':''}`;b.disabled=!x.available;b.innerHTML=`<span class="personnel-avatar">${techInitials(x.display_name)}</span><strong class="personnel-name">${x.display_name}</strong><span class="personnel-title">${x.title||''}</span><span class="personnel-meta">${techLabels[x.availability_status]||x.availability_status}</span>`;if(x.available)b.onclick=()=>{selectedTechnician=x;techInput.value=x.id;renderTech(rows);updateSummary()};g.append(b)});setText('[data-personnel-message]',message)};
const loadTechnicians=async()=>{if(!selectedService||!selectedDate||!selectedTime)return;const r=++techRequest;if(techController)techController.abort();techController=new AbortController();renderTech([],'Memuat teknisi tersedia...');try{const res=await fetch(`${technicianUrl}?date=${selectedDate}&time=${selectedTime}&jenis_servis_id=${selectedService}`,{headers:{Accept:'application/json'},signal:techController.signal});const data=await res.json();if(r===techRequest)renderTech(data.technicians||[])}catch(e){if(e.name!=='AbortError')renderTech([],'Tidak ada preferensi teknisi.')}};
const collapsed=()=>{const v=vehicle(),t=service(),f=finish();setText('[data-collapsed-schedule-main]',`${v?.name||'Kendaraan belum dipilih'} · ${t?.name||'Servis belum dipilih'}`);setText('[data-collapsed-schedule-meta]',`${formatDate(selectedDate)} · ${selectedTime?`${selectedTime} – ${f||'—'} WIB`:'Waktu belum dipilih'}`);setText('[data-collapsed-complaint]',$('#booking-complaint')?.value||'-');setText('[data-collapsed-note]',$('#booking-note')?.value||'-')};
const setStep=n=>{if(n===2&&!valid())return;if(n===3&&!valid())return;step=n;$$('[data-panel]').forEach(p=>p.hidden=+p.dataset.panel!==step);$('[data-collapsed-schedule]').hidden=step===1;$('[data-collapsed-detail]').hidden=step<3;$$('[data-step-button]').forEach(b=>{const no=+b.dataset.stepButton;b.classList.toggle('is-active',no===step);b.classList.toggle('is-complete',no<step);b.disabled=no>step;b.toggleAttribute('aria-current',no===step);});collapsed();updateSummary();gate();window.scrollTo({top:Math.max(0,root.offsetTop-88),behavior:'smooth'})};
vehicleSelect?.addEventListener('change',e=>{vehicleInput.value=e.target.value;updateSummary();gate()});$$('[data-service-choice]').forEach(b=>b.onclick=()=>{selectedService=b.dataset.serviceChoice;serviceInput.value=selectedService;userPickedDay=false;selectedDate='';selectedTime='';monthDays={};daySlots=[];dateInput.value='';timeInput.value='';selectedTechnician=null;techInput.value='';$$('[data-service-choice]').forEach(x=>x.classList.toggle('is-selected',x===b));syncPromoEligibility();renderUnclaimedCoupons();resetSlotGrid('Pilih tanggal pada kalender di atas untuk melihat jam kedatangan servis ini.');updateSummary();gate();renderCalendar();loadMonth()});$('[data-month-today]')?.addEventListener('click',()=>{month=root.dataset.currentMonth;monthDays={};renderCalendar();loadMonth()});$('[data-month-prev]')?.addEventListener('click',()=>{if(month<=root.dataset.currentMonth)return;const[y,m]=month.split('-').map(Number),d=new Date(y,m-2,1);month=`${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`;monthDays={};renderCalendar();loadMonth()});$('[data-month-next]')?.addEventListener('click',()=>{const[y,m]=month.split('-').map(Number),d=new Date(y,m,1);month=`${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`;monthDays={};renderCalendar();loadMonth()});$$('[data-next-step],[data-prev-step]').forEach(b=>b.onclick=()=>setStep(+(b.dataset.nextStep||b.dataset.prevStep)));
const scheduleModal=$('[data-schedule-modal]');let modalMonth=month,modalDate='',modalTime='',modalMonthDays={},modalSlots=[],modalMonthReq=0,modalDayReq=0;
const syncModalSave=()=>{const save=$('[data-schedule-modal-save]');if(save)save.disabled=!Boolean(modalDate&&modalTime&&modalSlots.some(x=>x.time===modalTime&&x.available))};
const renderModalCalendar=()=>{const g=$('[data-modal-calendar-grid]');if(!g)return;g.innerHTML='';const[y,m]=modalMonth.split('-').map(Number),first=(new Date(y,m-1,1).getDay()+6)%7,total=new Date(y,m,0).getDate();for(let i=0;i<first;i++){const e=document.createElement('span');e.className='schedule-day is-empty';g.append(e)}for(let d=1;d<=total;d++){const value=`${y}-${String(m).padStart(2,'0')}-${String(d).padStart(2,'0')}`;let data=modalMonthDays[value];if(!data){if(value<todayStr){data={status:'past'}}else if(!selectedService){data={status:'unavailable'}}else{const dow=new Date(y,m-1,d).getDay();data=(dow===0||dow===6)?{status:'unavailable'}:{status:'available'}}}const isToday=value===todayStr,b=document.createElement('button');b.type='button';b.className=`schedule-day${modalDate===value?' is-selected':''}${isToday?' is-today':''}`;b.dataset.status=data.status;b.disabled=!selectedService||!['available','almost_full'].includes(data.status);const label=data.status==='available'?'Tersedia':data.status==='almost_full'?(data.available_slots?`Sisa ${data.available_slots}`:'Terbatas'):data.status==='full'?'Penuh':data.status==='past'?'Lewat':'Tutup';b.innerHTML=`<strong>${d}</strong><span class="day-meta">${label}</span>`;if(!b.disabled)b.addEventListener('click',()=>loadModalDay(value,false));g.append(b)}setText('[data-modal-month-label]',monthLabel(modalMonth));if($('[data-modal-month-prev]'))$('[data-modal-month-prev]').disabled=modalMonth<=root.dataset.currentMonth;if($('[data-modal-month-today]'))$('[data-modal-month-today]').hidden=modalMonth===root.dataset.currentMonth};
const renderModalSlots=()=>{const g=$('[data-modal-slot-grid]');if(!g)return;g.innerHTML='';if(!modalDate){g.innerHTML=`<div class="slot-empty-notice"><span class="slot-empty-icon" aria-hidden="true">${clockSvg}</span><strong>Pilih Tanggal</strong><p>Klik tanggal yang tersedia pada kalender di atas.</p></div>`;syncModalSave();return}if(!modalSlots.length){g.innerHTML=`<div class="slot-empty-notice" style="border-color:#fecdd3;background:#fff1f2;"><span class="slot-empty-icon" style="color:#be123c;border-color:#fecdd3;" aria-hidden="true">${alertSvg}</span><strong style="color:#9f1239;">Tidak Ada Jam Tersedia</strong><p style="color:#be123c;">Silakan pilih tanggal lain di kalender atas.</p></div>`;syncModalSave();return}const t=service();modalSlots.forEach(x=>{const b=document.createElement('button');b.type='button';b.className=`schedule-slot ${modalTime===x.time?'is-selected':''}`;b.dataset.status=x.status;b.disabled=!x.available;let endText='';if(t&&x.available){const[sh,sm]=x.time.split(':').map(Number),sd=new Date(2000,0,1,sh,sm+t.duration);endText=` – ${String(sd.getHours()).padStart(2,'0')}:${String(sd.getMinutes()).padStart(2,'0')}`}const sub=modalTime===x.time?'✓ Jam dipilih':x.status==='available'?`Sisa ${x.capacity_remaining} slot`:x.status==='almost_full'?`Sisa ${x.capacity_remaining}`:x.status==='full'?'Penuh':'Tidak tersedia';b.innerHTML=`<span class="slot-time-row"><strong>${x.time}${endText}</strong><span class="slot-tz">WIB</span></span><span class="slot-sub">${sub}</span>`;if(x.available)b.addEventListener('click',()=>{modalTime=x.time;renderModalSlots();syncModalSave()});g.append(b)});syncModalSave()};
const loadModalMonth=async()=>{if(!selectedService)return;const r=++modalMonthReq;setStatus('[data-modal-calendar-status]','Memuat ketersediaan jadwal…');try{const res=await fetch(`${availabilityUrl}?month=${encodeURIComponent(modalMonth)}&jenis_servis_id=${selectedService}`,{headers:{Accept:'application/json'}});if(!res.ok)throw Error();const data=await res.json();if(r!==modalMonthReq)return;modalMonthDays=Object.fromEntries((data.days||[]).map(x=>[x.date,x]));setStatus('[data-modal-calendar-status]','');renderModalCalendar()}catch(_){if(r===modalMonthReq){setStatus('[data-modal-calendar-status]','Gagal memuat jadwal bulan ini.',true);renderModalCalendar()}}};
const loadModalDay=async(date,preserveTime=false)=>{if(!selectedService)return;const r=++modalDayReq;modalDate=date;if(!preserveTime)modalTime='';modalSlots=[];renderModalCalendar();const badge=$('[data-modal-slot-date]');if(badge){badge.textContent=formatDate(date);badge.style.display='inline-flex'}setStatus('[data-modal-slot-status]','Memuat jam kedatangan…');syncModalSave();try{const res=await fetch(`${availabilityUrl}?date=${date}&jenis_servis_id=${selectedService}`,{headers:{Accept:'application/json'}});if(!res.ok)throw Error();const data=await res.json();if(r!==modalDayReq)return;modalSlots=data.slots||[];if(preserveTime&&modalTime&&!modalSlots.some(x=>x.time===modalTime&&x.available))modalTime='';setStatus('[data-modal-slot-status]','');renderModalSlots()}catch(_){if(r===modalDayReq){modalSlots=[];setStatus('[data-modal-slot-status]','Gagal memuat slot jam.',true);renderModalSlots()}}};
const closeScheduleModal=()=>{if(!scheduleModal)return;scheduleModal.hidden=true;document.body.style.overflow=''};
const openScheduleModal=()=>{if(!scheduleModal)return;modalMonth=selectedDate?selectedDate.slice(0,7):month;modalDate=selectedDate;modalTime=selectedTime;modalMonthDays={...monthDays};modalSlots=[...daySlots];const t=service();setText('[data-modal-service-label]',t?`Layanan: ${t.name} · Pilih tanggal dan jam baru yang tersedia.`:'Pilih tanggal dan jam baru yang tersedia.');const badge=$('[data-modal-slot-date]');if(badge){badge.textContent=modalDate?formatDate(modalDate):'';badge.style.display=modalDate?'inline-flex':'none'}scheduleModal.hidden=false;document.body.style.overflow='hidden';renderModalCalendar();renderModalSlots();loadModalMonth();if(modalDate)loadModalDay(modalDate,true)};
$('[data-edit-schedule]')?.addEventListener('click',openScheduleModal);
$$('[data-schedule-modal-close]').forEach(b=>b.addEventListener('click',closeScheduleModal));
scheduleModal?.addEventListener('click',e=>{if(e.target===scheduleModal)closeScheduleModal()});
document.addEventListener('keydown',e=>{if(e.key==='Escape'&&scheduleModal&&!scheduleModal.hidden)closeScheduleModal()});
$('[data-modal-month-today]')?.addEventListener('click',()=>{modalMonth=root.dataset.currentMonth;modalMonthDays={};renderModalCalendar();loadModalMonth()});
$('[data-modal-month-prev]')?.addEventListener('click',()=>{if(modalMonth<=root.dataset.currentMonth)return;const[y,m]=modalMonth.split('-').map(Number),d=new Date(y,m-2,1);modalMonth=`${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`;modalMonthDays={};renderModalCalendar();loadModalMonth()});
$('[data-modal-month-next]')?.addEventListener('click',()=>{const[y,m]=modalMonth.split('-').map(Number),d=new Date(y,m,1);modalMonth=`${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`;modalMonthDays={};renderModalCalendar();loadModalMonth()});
$('[data-schedule-modal-save]')?.addEventListener('click',()=>{if(!modalDate||!modalTime)return;const changed=modalDate!==selectedDate||modalTime!==selectedTime;month=modalMonth;monthDays={...modalMonthDays};selectedDate=modalDate;selectedTime=modalTime;daySlots=[...modalSlots];dateInput.value=selectedDate;timeInput.value=selectedTime;if(changed){selectedTechnician=null;techInput.value='';loadTechnicians()}const badge=$('[data-slot-date-badge]');if(badge){badge.textContent=formatDate(selectedDate);badge.style.display='inline-flex'}setText('[data-slot-description]',`Menampilkan pilihan jam kedatangan untuk ${formatDate(selectedDate)}:`);renderCalendar();renderSlots();collapsed();updateSummary();gate();persistProgress();closeScheduleModal()});
$('[data-edit-detail]')?.addEventListener('click',()=>setStep(2));['booking-complaint','booking-note'].forEach(id=>$('#'+id)?.addEventListener('input',()=>{updateSummary();collapsed()}));
const confirmModal=$('[data-confirm-modal]'),bookingForm=$('[data-schedule-form]');
const closeConfirmModal=()=>{if(!confirmModal)return;confirmModal.hidden=true;document.body.style.overflow=''};
const openConfirmModal=()=>{if(!confirmModal||!valid())return;const v=vehicle(),t=service(),pr=promo(),f=finish(),pInfo=calcPricing(t,pr);setText('[data-modal-confirm-vehicle]',v?`${v.name} · ${v.plate}`:'Belum dipilih');setText('[data-modal-confirm-service]',t?.name);setText('[data-modal-confirm-technician]',selectedTechnician?.display_name||'Tidak Ada Preferensi');setText('[data-modal-confirm-schedule]',`${formatDate(selectedDate)} · ${selectedTime}${f?` – ${f}`:''} WIB`);setText('[data-modal-confirm-promo]',pr?`${pr.code} · ${pr.benefit}`:'Tidak Pakai Kupon');setText('[data-modal-confirm-price]',pInfo.base);const dRow=$('[data-modal-confirm-discount-row]'),dCode=$('[data-modal-confirm-discount-code]'),orig=$('[data-modal-confirm-original]');if(dRow)dRow.hidden=!pInfo.hasPromo;if(dCode)dCode.textContent=pInfo.code;setText('[data-modal-confirm-discount]',pInfo.disc);if(orig){orig.hidden=!pInfo.strike;orig.textContent=pInfo.strike}setText('[data-modal-confirm-total]',pInfo.total);confirmModal.hidden=false;document.body.style.overflow='hidden'};
$('[data-summary-submit]')?.addEventListener('click',openConfirmModal);
$$('[data-confirm-modal-close]').forEach(b=>b.addEventListener('click',closeConfirmModal));
confirmModal?.addEventListener('click',e=>{if(e.target===confirmModal)closeConfirmModal()});
document.addEventListener('keydown',e=>{if(e.key==='Escape'&&confirmModal&&!confirmModal.hidden)closeConfirmModal()});
$('[data-confirm-modal-submit]')?.addEventListener('click',e=>{const btn=e.currentTarget;btn.disabled=true;btn.textContent='Mengirim...';try{sessionStorage.removeItem(progressKey)}catch(_){}bookingForm?.submit()});
dateInput.value=selectedDate;timeInput.value=selectedTime;updateSummary();gate();renderCalendar();if(selectedDate)loadDay(selectedDate,true);else resetSlotGrid();loadMonth();})();
</script>
@endpush
