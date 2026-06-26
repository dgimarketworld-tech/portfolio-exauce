<?php
require_once __DIR__.'/../../backend/admin_required.php';
$adm=$currentAdmin;
$initials=strtoupper(substr($adm['first_name']??'A',0,1).substr($adm['last_name']??'M',0,1));
?><!DOCTYPE html>
<html lang="fr"><head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>GTB Admin — <?php echo $title??'';?></title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@200;300;400;500;600;700;800&family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500;600&display=swap');
:root{
--admin-bg:#0F172A;--admin-bg-deeper:#020617;--admin-bg-mid:#1E293B;
--accent:#3B82F6;--accent-light:#60A5FA;--accent-deep:#1D4ED8;
--white:#FFFFFF;--off:#F1F5F9;--gray50:#F8FAFC;--gray100:#F1F5F9;--gray200:#E2E8F0;--gray300:#CBD5E1;--gray400:#94A3B8;--gray500:#64748B;--gray600:#475569;--gray700:#334155;--gray800:#1E293B;--gray900:#0F172A;
--success:#10B981;--warning:#F59E0B;--danger:#EF4444;--info:#0EA5E9;--purple:#8B5CF6;
--sh-sm:0 1px 3px rgba(15,23,42,.06);--sh-md:0 6px 24px rgba(15,23,42,.08);--sh-lg:0 20px 50px rgba(15,23,42,.12);--sh-xl:0 30px 70px rgba(15,23,42,.16);
--r-sm:6px;--r-md:10px;--r-lg:16px;--r-xl:24px;--r-full:9999px;
--ease:cubic-bezier(.25,.46,.45,.94);--bounce:cubic-bezier(.34,1.56,.64,1);
--sidebar-w:248px;--topbar-h:60px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;font-size:15px;}
body{font-family:'DM Sans',sans-serif;background:#F1F5F9;color:var(--gray700);overflow-x:hidden;-webkit-font-smoothing:antialiased;}
::-webkit-scrollbar{width:6px;height:6px;}
::-webkit-scrollbar-track{background:transparent;}
::-webkit-scrollbar-thumb{background:var(--gray300);border-radius:99px;}
::-webkit-scrollbar-thumb:hover{background:var(--gray400);}
.dash-layout{min-height:100vh;position:relative;}
.sidebar-overlay{position:fixed;inset:0;background:rgba(2,6,23,.5);backdrop-filter:blur(2px);opacity:0;visibility:hidden;transition:opacity .3s var(--ease),visibility .3s var(--ease);z-index:150;}
.sidebar-overlay.show{opacity:1;visibility:visible;}
.sidebar{background:var(--admin-bg);position:fixed;top:0;left:0;bottom:0;width:var(--sidebar-w);display:flex;flex-direction:column;z-index:200;overflow:hidden;transition:transform .35s var(--ease);border-right:1px solid var(--gray800);}
.sidebar-logo{display:flex;align-items:center;gap:.65rem;padding:1.25rem 1.4rem;text-decoration:none;border-bottom:1px solid rgba(255,255,255,.05);}
.sidebar-logo-mark{width:36px;height:36px;background:linear-gradient(135deg,var(--accent),var(--accent-deep));border-radius:8px;display:flex;align-items:center;justify-content:center;font-family:'Sora',sans-serif;font-weight:800;font-size:.74rem;color:white;flex-shrink:0;letter-spacing:-.02em;}
.sidebar-logo-text{display:flex;flex-direction:column;line-height:1;}
.sidebar-logo-text .top{font-family:'Sora',sans-serif;font-weight:700;font-size:.84rem;color:white;}
.sidebar-logo-text .bot{font-size:.58rem;color:var(--accent-light);letter-spacing:.1em;text-transform:uppercase;margin-top:3px;font-weight:600;}
.sidebar-user{padding:.85rem 1.4rem;border-bottom:1px solid rgba(255,255,255,.05);display:flex;align-items:center;gap:.7rem;}
.sidebar-avatar{width:34px;height:34px;border-radius:8px;background:linear-gradient(135deg,var(--accent),var(--accent-deep));display:flex;align-items:center;justify-content:center;font-family:'Sora',sans-serif;font-weight:700;font-size:.72rem;color:white;flex-shrink:0;}
.sidebar-user-info{flex:1;overflow:hidden;}
.sidebar-user-name{font-size:.8rem;font-weight:600;color:white;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.sidebar-user-role{font-size:.6rem;color:#EF4444;letter-spacing:.08em;text-transform:uppercase;font-weight:700;}
.sidebar-nav{flex:1;padding:.75rem 0;overflow-y:auto;-webkit-overflow-scrolling:touch;}
.sidebar-section-label{font-size:.55rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,.25);padding:.65rem 1.4rem .25rem;}
.sidebar-link{display:flex;align-items:center;gap:.7rem;padding:.55rem 1.4rem;margin:.05rem .6rem;border-radius:8px;text-decoration:none;color:rgba(255,255,255,.55);font-size:.8rem;font-weight:500;transition:all .2s var(--ease);position:relative;}
.sidebar-link:hover{color:white;background:rgba(255,255,255,.06);}
.sidebar-link.active{color:white;background:rgba(59,130,246,.15);border:1px solid rgba(59,130,246,.25);}
.sidebar-link.active::before{content:'';position:absolute;left:-.6rem;top:50%;transform:translateY(-50%);width:3px;height:60%;background:var(--accent);border-radius:0 3px 3px 0;}
.sidebar-icon{width:18px;height:18px;display:flex;align-items:center;justify-content:center;font-size:.95rem;flex-shrink:0;opacity:.7;transition:opacity .2s;}
.sidebar-link:hover .sidebar-icon,.sidebar-link.active .sidebar-icon{opacity:1;}
.sidebar-badge{margin-left:auto;background:var(--danger);color:white;font-size:.55rem;font-weight:700;padding:.15rem .45rem;border-radius:var(--r-full);}
.sidebar-footer{padding:.85rem 1.4rem;border-top:1px solid rgba(255,255,255,.05);}
.sidebar-footer-link{display:flex;align-items:center;gap:.6rem;font-size:.76rem;color:rgba(255,255,255,.4);text-decoration:none;padding:.4rem 0;transition:color .2s;}
.sidebar-footer-link:hover{color:rgba(255,255,255,.85);}
.sidebar-back{display:flex;align-items:center;gap:.55rem;padding:.6rem .8rem;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:8px;font-size:.75rem;color:rgba(255,255,255,.65);text-decoration:none;margin-bottom:.6rem;transition:all .2s;}
.sidebar-back:hover{background:rgba(255,255,255,.08);color:white;}
.topbar{position:fixed;top:0;left:var(--sidebar-w);right:0;height:var(--topbar-h);background:rgba(255,255,255,.92);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-bottom:1px solid var(--gray200);display:flex;align-items:center;justify-content:space-between;padding:0 1.75rem;z-index:100;gap:1rem;transition:left .35s var(--ease);}
.topbar-left{display:flex;align-items:center;gap:1rem;min-width:0;}
.topbar-page-title{font-family:'Sora',sans-serif;font-weight:700;font-size:.95rem;color:var(--gray900);white-space:nowrap;}
.topbar-breadcrumb{font-size:.72rem;color:var(--gray400);white-space:nowrap;}
.topbar-breadcrumb a{color:var(--gray400);text-decoration:none;transition:color .2s;}
.topbar-breadcrumb a:hover{color:var(--accent);}
.topbar-search{display:flex;align-items:center;gap:.55rem;background:var(--gray50);border:1.5px solid var(--gray200);border-radius:var(--r-full);padding:.4rem .95rem;transition:all .2s;flex:1;max-width:360px;}
.topbar-search:focus-within{border-color:var(--accent);background:white;box-shadow:0 0 0 3px rgba(59,130,246,.1);}
.topbar-search input{border:none;outline:none;background:none;font-family:'DM Sans',sans-serif;font-size:.8rem;color:var(--gray800);width:100%;}
.topbar-search input::placeholder{color:var(--gray400);}
.topbar-right{display:flex;align-items:center;gap:.65rem;flex-shrink:0;margin-left:auto;}
.topbar-btn{width:36px;height:36px;border-radius:8px;background:var(--gray50);border:1.5px solid var(--gray200);display:flex;align-items:center;justify-content:center;cursor:pointer;position:relative;font-size:.95rem;transition:all .2s;color:var(--gray600);flex-shrink:0;}
.topbar-btn:hover{border-color:var(--accent);color:var(--accent);background:white;}
.env-badge{display:inline-flex;align-items:center;gap:.3rem;padding:.25rem .65rem;font-size:.62rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;border-radius:var(--r-full);background:rgba(239,68,68,.1);color:var(--danger);border:1px solid rgba(239,68,68,.2);}
.dash-main{margin-left:var(--sidebar-w);margin-top:var(--topbar-h);padding:1.75rem;min-height:calc(100vh - var(--topbar-h));transition:margin-left .35s var(--ease);}
.page-header{margin-bottom:1.5rem;}
.page-header-top{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;}
.page-title{font-family:'Sora',sans-serif;font-weight:700;font-size:clamp(1.25rem,2.2vw,1.55rem);color:var(--gray900);line-height:1.2;letter-spacing:-.01em;}
.page-sub{font-size:.82rem;color:var(--gray500);margin-top:.3rem;line-height:1.5;}
.page-actions{display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;}
.card{background:white;border:1px solid var(--gray200);border-radius:var(--r-lg);box-shadow:var(--sh-sm);}
.card-pad{padding:1.25rem;}
.card-pad-lg{padding:1.75rem;}
.card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.1rem;gap:1rem;flex-wrap:wrap;}
.card-title{font-family:'Sora',sans-serif;font-weight:700;font-size:.88rem;color:var(--gray900);}
.card-sub{font-size:.72rem;color:var(--gray400);margin-top:.1rem;}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;font-family:'DM Sans',sans-serif;font-weight:600;font-size:.78rem;border:none;cursor:pointer;text-decoration:none;border-radius:8px;padding:.55rem 1.1rem;transition:all .2s var(--ease);white-space:nowrap;}
.btn-primary{background:var(--accent);color:white;box-shadow:0 1px 3px rgba(59,130,246,.3);}
.btn-primary:hover{background:var(--accent-deep);transform:translateY(-1px);box-shadow:0 6px 16px rgba(59,130,246,.35);}
.btn-dark{background:var(--gray900);color:white;}
.btn-dark:hover{background:var(--gray800);transform:translateY(-1px);}
.btn-outline{background:white;color:var(--gray700);border:1.5px solid var(--gray200);}
.btn-outline:hover{border-color:var(--accent);color:var(--accent);}
.btn-ghost{background:transparent;color:var(--gray500);}
.btn-ghost:hover{color:var(--gray900);background:var(--gray100);}
.btn-success{background:var(--success);color:white;}
.btn-success:hover{background:#0e9970;transform:translateY(-1px);}
.btn-danger{background:rgba(239,68,68,.1);color:var(--danger);}
.btn-danger:hover{background:var(--danger);color:white;}
.btn-sm{padding:.35rem .85rem;font-size:.72rem;}
.btn-xs{padding:.25rem .65rem;font-size:.68rem;}
.btn-icon{padding:.4rem;aspect-ratio:1;}
.badge{display:inline-flex;align-items:center;gap:.25rem;font-size:.66rem;font-weight:600;letter-spacing:.02em;padding:.18rem .55rem;border-radius:var(--r-full);}
.badge-blue{background:rgba(59,130,246,.1);color:var(--accent);}
.badge-success{background:rgba(16,185,129,.1);color:var(--success);}
.badge-warning{background:rgba(245,158,11,.12);color:#B45309;}
.badge-danger{background:rgba(239,68,68,.1);color:var(--danger);}
.badge-gray{background:var(--gray100);color:var(--gray600);}
.badge-purple{background:rgba(139,92,246,.1);color:var(--purple);}
.toast-container{position:fixed;bottom:1.25rem;right:1.25rem;z-index:2000;display:flex;flex-direction:column;gap:.5rem;}
.toast{background:white;border-radius:10px;box-shadow:var(--sh-lg);padding:.8rem 1.1rem;display:flex;align-items:center;gap:.65rem;font-size:.8rem;font-weight:500;color:var(--gray800);border-left:3px solid var(--accent);transform:translateX(120%);opacity:0;transition:all .4s var(--bounce);min-width:240px;max-width:340px;}
.toast.show{transform:translateX(0);opacity:1;}
.toast.success{border-left-color:var(--success);}
.toast.error{border-left-color:var(--danger);}
.toast.info{border-left-color:var(--info);}
.toast.warning{border-left-color:var(--warning);}
.toast-icon{font-size:1.1rem;flex-shrink:0;}
.sidebar-toggle{display:none;cursor:pointer;width:36px;height:36px;border-radius:8px;background:var(--gray50);border:1.5px solid var(--gray200);align-items:center;justify-content:center;font-size:1rem;color:var(--gray600);transition:all .2s;flex-shrink:0;}
.sidebar-toggle:hover{border-color:var(--accent);color:var(--accent);}
@keyframes fadeInUp{from{opacity:0;transform:translateY(12px);}to{opacity:1;transform:translateY(0);}}
@keyframes scaleIn{from{opacity:0;transform:scale(.96);}to{opacity:1;transform:scale(1);}}
.anim-up{animation:fadeInUp .45s var(--ease) both;}
.anim-scale{animation:scaleIn .4s var(--bounce) both;}
.d1{animation-delay:.04s;}.d2{animation-delay:.08s;}.d3{animation-delay:.12s;}.d4{animation-delay:.16s;}.d5{animation-delay:.20s;}.d6{animation-delay:.24s;}
/* STATS GRID */
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;}
.stat-card{background:white;border:1px solid var(--gray200);border-radius:var(--r-lg);padding:1.1rem 1.25rem;box-shadow:var(--sh-sm);transition:all .25s var(--ease);position:relative;overflow:hidden;}
.stat-card:hover{transform:translateY(-2px);box-shadow:var(--sh-md);}
.stat-row{display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem;}
.stat-info{flex:1;min-width:0;}
.stat-label{font-size:.66rem;font-weight:600;color:var(--gray400);letter-spacing:.06em;text-transform:uppercase;margin-bottom:.3rem;}
.stat-value{font-family:'Sora',sans-serif;font-weight:800;font-size:1.45rem;color:var(--gray900);line-height:1;margin-bottom:.35rem;}
.stat-trend{display:inline-flex;align-items:center;gap:.25rem;font-size:.66rem;font-weight:600;padding:.15rem .45rem;border-radius:var(--r-full);}
.stat-trend.up{background:rgba(16,185,129,.1);color:var(--success);}
.stat-trend.down{background:rgba(239,68,68,.1);color:var(--danger);}
.stat-trend.neutral{background:var(--gray100);color:var(--gray500);}
.stat-icon-box{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.15rem;flex-shrink:0;}
.stat-icon-box.blue{background:rgba(59,130,246,.1);color:var(--accent);}
.stat-icon-box.green{background:rgba(16,185,129,.1);color:var(--success);}
.stat-icon-box.amber{background:rgba(245,158,11,.12);color:#B45309;}
.stat-icon-box.red{background:rgba(239,68,68,.1);color:var(--danger);}
.stat-icon-box.purple{background:rgba(139,92,246,.1);color:var(--purple);}
.stat-icon-box.gray{background:var(--gray100);color:var(--gray600);}
/* FILTER BAR */
.filter-bar{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;margin-bottom:1rem;}
.filter-chip{display:inline-flex;align-items:center;gap:.3rem;padding:.4rem .85rem;border-radius:var(--r-full);font-size:.74rem;font-weight:600;cursor:pointer;background:white;border:1.5px solid var(--gray200);color:var(--gray600);transition:all .2s var(--ease);}
.filter-chip:hover{border-color:var(--accent);color:var(--gray900);}
.filter-chip.active{background:var(--gray900);border-color:var(--gray900);color:white;}
.filter-chip .chip-count{font-size:.62rem;font-weight:700;background:var(--gray100);color:var(--gray600);padding:.08rem .4rem;border-radius:var(--r-full);}
.filter-chip.active .chip-count{background:rgba(255,255,255,.15);color:white;}
.filter-spacer{flex:1;}
.tx-select{font-family:'DM Sans',sans-serif;font-size:.76rem;font-weight:500;color:var(--gray800);background:white;border:1.5px solid var(--gray200);border-radius:var(--r-full);padding:.4rem 1rem;cursor:pointer;outline:none;transition:all .2s;}
.tx-select:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(59,130,246,.1);}
/* TABLE */
.adm-table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch;}
.adm-table{width:100%;border-collapse:collapse;min-width:720px;}
.adm-table th{font-size:.62rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--gray400);padding:.65rem 1rem;text-align:left;border-bottom:1px solid var(--gray200);white-space:nowrap;background:var(--gray50);}
.adm-table th.right{text-align:right;}
.adm-table td{padding:.85rem 1rem;font-size:.8rem;color:var(--gray700);border-bottom:1px solid var(--gray100);vertical-align:middle;}
.adm-table tbody tr{transition:background .15s;}
.adm-table tbody tr:hover{background:var(--gray50);}
.adm-table tbody tr:last-child td{border-bottom:none;}
.adm-table .cell-id{font-family:'JetBrains Mono',monospace;font-size:.72rem;color:var(--gray500);}
.adm-table .cell-name{font-weight:600;color:var(--gray900);}
.adm-table .cell-amount{font-family:'Sora',sans-serif;font-weight:700;text-align:right;white-space:nowrap;}
.adm-table .cell-amount.pos{color:var(--success);}
.adm-table .cell-amount.neg{color:var(--danger);}
.cell-user{display:flex;align-items:center;gap:.6rem;}
.cell-avatar{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent-deep));display:flex;align-items:center;justify-content:center;font-family:'Sora',sans-serif;font-weight:700;font-size:.65rem;color:white;flex-shrink:0;}
.cell-user-info{min-width:0;}
.cell-user-mail{font-size:.7rem;color:var(--gray500);margin-top:.1rem;}
.action-btns{display:flex;gap:.3rem;justify-content:flex-end;}
.act-btn{width:28px;height:28px;border-radius:6px;background:var(--gray50);border:1px solid var(--gray200);cursor:pointer;font-size:.85rem;color:var(--gray500);display:flex;align-items:center;justify-content:center;transition:all .2s;}
.act-btn:hover{background:white;border-color:var(--accent);color:var(--accent);}
.act-btn.danger:hover{border-color:var(--danger);color:var(--danger);}
.act-btn.success:hover{border-color:var(--success);color:var(--success);}
/* EMPTY */
.empty-state{text-align:center;padding:2.75rem 1rem;display:none;}
.empty-state.show{display:block;}
.empty-state-ico{font-size:2rem;opacity:.4;margin-bottom:.5rem;}
.empty-state-txt{font-family:'Sora',sans-serif;font-weight:600;color:var(--gray800);font-size:.88rem;}
.empty-state-sub{font-size:.78rem;color:var(--gray400);margin-top:.25rem;}
/* MODAL */
.modal-overlay{position:fixed;inset:0;background:rgba(2,6,23,.6);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);opacity:0;visibility:hidden;transition:all .3s var(--ease);z-index:300;}
.modal-overlay.open{opacity:1;visibility:visible;}
.modal{position:fixed;top:50%;left:50%;transform:translate(-50%,-48%) scale(.96);width:500px;max-width:94vw;max-height:90vh;background:white;border-radius:var(--r-xl);box-shadow:var(--sh-xl);z-index:301;opacity:0;visibility:hidden;transition:all .3s var(--bounce);display:flex;flex-direction:column;overflow:hidden;}
.modal.open{transform:translate(-50%,-50%) scale(1);opacity:1;visibility:visible;}
.modal-head{padding:1.25rem;border-bottom:1px solid var(--gray100);display:flex;align-items:center;justify-content:space-between;gap:1rem;}
.modal-title{font-family:'Sora',sans-serif;font-weight:700;font-size:.95rem;color:var(--gray900);}
.modal-sub{font-size:.72rem;color:var(--gray400);margin-top:.1rem;}
.modal-close{width:32px;height:32px;border-radius:8px;background:var(--gray50);border:1px solid var(--gray200);cursor:pointer;font-size:.95rem;color:var(--gray500);display:flex;align-items:center;justify-content:center;transition:all .2s;}
.modal-close:hover{background:var(--gray100);}
.modal-body{padding:1.25rem;overflow-y:auto;flex:1;}
.modal-foot{padding:1rem 1.25rem;border-top:1px solid var(--gray100);display:flex;gap:.5rem;}
.modal-foot .btn{flex:1;}
.form-group{margin-bottom:.85rem;}
.form-label{display:block;font-size:.7rem;font-weight:600;color:var(--gray700);margin-bottom:.3rem;letter-spacing:.02em;}
.form-input,.form-select,.form-textarea{width:100%;padding:.55rem .8rem;font-family:'DM Sans',sans-serif;font-size:.8rem;color:var(--gray800);background:var(--gray50);border:1.5px solid var(--gray200);border-radius:8px;outline:none;transition:all .2s;}
.form-input:focus,.form-select:focus,.form-textarea:focus{border-color:var(--accent);background:white;box-shadow:0 0 0 3px rgba(59,130,246,.1);}
.form-textarea{resize:vertical;min-height:80px;font-family:'DM Sans',sans-serif;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:.6rem;}
/* RESPONSIVE */
@media (max-width:1100px){.stats-grid{grid-template-columns:repeat(2,1fr);}}
@media (max-width:1024px){:root{--sidebar-w:220px;}.topbar-search{display:none;}}
@media (max-width:768px){
.sidebar{transform:translateX(-100%);width:260px;}.sidebar.open{transform:translateX(0);}
.topbar{left:0;padding:0 1rem;}.dash-main{margin-left:0;padding:1rem;}
.sidebar-toggle{display:flex;}.topbar-search{display:none;}
.topbar-page-title{font-size:.85rem;}
}
@media (max-width:560px){.stats-grid{grid-template-columns:1fr;}.page-actions{width:100%;}.page-actions .btn{flex:1;}.form-row{grid-template-columns:1fr;}}
@media (max-width:420px){.dash-main{padding:.85rem;}.topbar-breadcrumb{display:none;}.toast-container{left:1rem;right:1rem;bottom:1rem;}.toast{min-width:0;max-width:none;}.modal{max-height:94vh;}}
@media (prefers-reduced-motion:reduce){*,*::before,*::after{animation-duration:.01ms!important;animation-iteration-count:1!important;transition-duration:.01ms!important;scroll-behavior:auto!important;}}


.dash-grid{display:grid;grid-template-columns:1.6fr 1fr;gap:1.25rem;margin-bottom:1.25rem;}
.activity-card{padding:1.25rem;}
.activity-row{display:flex;align-items:flex-start;gap:.75rem;padding:.7rem 0;border-bottom:1px solid var(--gray100);}
.activity-row:last-child{border-bottom:none;}
.activity-dot{width:8px;height:8px;border-radius:50%;background:var(--accent);flex-shrink:0;margin-top:.35rem;}
.activity-dot.success{background:var(--success);}
.activity-dot.warning{background:var(--warning);}
.activity-dot.danger{background:var(--danger);}
.activity-body{flex:1;min-width:0;}
.activity-text{font-size:.78rem;color:var(--gray800);line-height:1.5;}
.activity-text strong{color:var(--gray900);}
.activity-meta{font-size:.66rem;color:var(--gray400);margin-top:.2rem;}
.quick-links{display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;}
.quick-link{padding:1rem;border:1.5px solid var(--gray200);border-radius:10px;text-decoration:none;color:var(--gray800);text-align:center;transition:all .2s var(--ease);background:white;}
.quick-link:hover{border-color:var(--accent);transform:translateY(-2px);box-shadow:var(--sh-md);color:var(--gray900);}
.quick-link-ico{font-size:1.4rem;margin-bottom:.5rem;}
.quick-link-label{font-size:.72rem;font-weight:600;}
@media (max-width:1100px){.dash-grid{grid-template-columns:1fr;}.quick-links{grid-template-columns:repeat(2,1fr);}}


.chips-bar{display:flex;gap:.4rem;flex-wrap:wrap;margin-bottom:1rem}
.chip{padding:.35rem .8rem;border-radius:999px;font-size:.74rem;font-weight:600;cursor:pointer;background:#fff;border:1.5px solid #e5e7eb;color:#6b7280;transition:.2s}
.chip.active{background:#1e293b;border-color:#1e293b;color:#fff}
.admin-search{display:flex;align-items:center;gap:.5rem;background:#fff;border:1.5px solid #e5e7eb;border-radius:999px;padding:.4rem .9rem;width:100%;max-width:360px}
.admin-search input{border:none;outline:none;font-size:.82rem;color:#1e293b;width:100%}
/* ═══ ALIAS classes admin (compatibilité HTML) ═══ */
.admin-layout{min-height:100vh;position:relative;}
.admin-topbar{position:fixed;top:0;left:var(--sidebar-w);right:0;height:var(--topbar-h);background:rgba(255,255,255,.92);backdrop-filter:blur(20px);border-bottom:1px solid var(--gray200);display:flex;align-items:center;justify-content:space-between;padding:0 1.75rem;z-index:100;gap:1rem;}
.admin-main{margin-left:var(--sidebar-w);margin-top:var(--topbar-h);padding:1.75rem;min-height:calc(100vh - var(--topbar-h));}
.nav-link{display:flex;align-items:center;gap:.7rem;padding:.55rem 1.4rem;margin:.05rem .6rem;border-radius:8px;text-decoration:none;color:rgba(255,255,255,.55);font-size:.8rem;font-weight:500;transition:all .2s;}
.nav-link:hover{color:white;background:rgba(255,255,255,.06);}
.nav-link.active{color:white;background:rgba(59,130,246,.15);border:1px solid rgba(59,130,246,.25);}
.nav-section-label{font-size:.55rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,.25);padding:.65rem 1.4rem .25rem;}
.nav-icon{width:18px;height:18px;display:flex;align-items:center;justify-content:center;font-size:.95rem;flex-shrink:0;opacity:.7;}
.nav-link:hover .nav-icon,.nav-link.active .nav-icon{opacity:1;}
.logo-mark{width:36px;height:36px;background:linear-gradient(135deg,var(--accent),var(--accent-deep));border-radius:8px;display:flex;align-items:center;justify-content:center;font-family:'Sora',sans-serif;font-weight:800;font-size:.74rem;color:white;flex-shrink:0;}
.logo-text{display:flex;flex-direction:column;line-height:1;}
.logo-text span:first-child{font-family:'Sora',sans-serif;font-weight:700;font-size:.84rem;color:white;}
.logo-text span:last-child{font-size:.58rem;color:var(--accent-light);letter-spacing:.1em;text-transform:uppercase;margin-top:3px;font-weight:600;}
.admin-user{padding:.85rem 1.4rem;border-bottom:1px solid rgba(255,255,255,.05);display:flex;align-items:center;gap:.7rem;}
.admin-av{width:34px;height:34px;border-radius:8px;background:linear-gradient(135deg,var(--accent),var(--accent-deep));display:flex;align-items:center;justify-content:center;font-family:'Sora',sans-serif;font-weight:700;font-size:.72rem;color:white;flex-shrink:0;}
.admin-name{font-size:.8rem;font-weight:600;color:white;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.admin-role{font-size:.6rem;color:#EF4444;letter-spacing:.08em;text-transform:uppercase;font-weight:700;}
.admin-card{background:white;border:1px solid var(--gray200);border-radius:var(--r-lg);padding:1.25rem;box-shadow:var(--sh-sm);overflow:hidden;}
.admin-table{width:100%;border-collapse:collapse;min-width:600px;}
.admin-table th{font-size:.62rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--gray400);padding:.65rem 1rem;text-align:left;border-bottom:1px solid var(--gray200);white-space:nowrap;background:var(--gray50);}
.admin-table td{padding:.82rem 1rem;font-size:.8rem;color:var(--gray700);border-bottom:1px solid var(--gray100);vertical-align:middle;}
.admin-table tbody tr{transition:background .15s;}
.admin-table tbody tr:hover{background:var(--gray50);}
.admin-table tbody tr:last-child td{border-bottom:none;}
.admin-btn{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;font-family:'DM Sans',sans-serif;font-weight:600;font-size:.76rem;border:1.5px solid var(--gray200);cursor:pointer;text-decoration:none;border-radius:8px;padding:.42rem .9rem;background:white;color:var(--gray700);transition:all .2s;white-space:nowrap;}
.admin-btn:hover{border-color:var(--accent);color:var(--accent);}
.admin-btn-xs{display:inline-flex;align-items:center;justify-content:center;font-family:'DM Sans',sans-serif;font-weight:600;font-size:.7rem;border:none;cursor:pointer;border-radius:6px;padding:.28rem .65rem;transition:all .2s;}
.admin-btn-danger{background:rgba(239,68,68,.1);color:var(--danger);}
.admin-btn-danger:hover{background:var(--danger);color:white;}
.admin-btn-success{background:rgba(16,185,129,.1);color:var(--success);}
.admin-btn-success:hover{background:var(--success);color:white;}
.admin-input{font-family:'DM Sans',sans-serif;font-size:.8rem;color:var(--gray800);background:white;border:1.5px solid var(--gray200);border-radius:8px;padding:.45rem .8rem;outline:none;transition:all .2s;}
.admin-input:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(59,130,246,.1);}
.admin-kpi-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem;}
.kpi-card{background:white;border:1px solid var(--gray200);border-radius:var(--r-lg);padding:1.1rem 1.25rem;box-shadow:var(--sh-sm);display:flex;align-items:center;gap:.85rem;}
.kpi-icon{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;}
.kpi-label{font-size:.68rem;font-weight:600;color:var(--gray400);letter-spacing:.04em;text-transform:uppercase;margin-bottom:.25rem;}
.kpi-value{font-family:'Sora',sans-serif;font-weight:800;font-size:1.3rem;color:var(--gray900);line-height:1;}
@media(max-width:768px){.admin-topbar{left:0;padding:0 1rem;}.admin-main{margin-left:0;padding:1rem;}}</style><link rel="stylesheet" href="../mobile.css">
<link rel="stylesheet" href="../../assets/gtb-ds.css">
</head>
<body><div class="admin-layout">
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo"><div class="logo-mark">GTB</div><div class="logo-text"><span>Global Trust Bank</span><span>Administration</span></div></div>
  <div class="admin-user"><div class="admin-av"><?php echo $initials;?></div><div><div class="admin-name"><?php echo htmlspecialchars(($adm['first_name']??'').' '.($adm['last_name']??''),ENT_QUOTES,'UTF-8');?></div><div class="admin-role"><?php echo ucfirst($adm['role']??'admin');?></div></div></div>
  <nav class="sidebar-nav">
    <div class="nav-section-label">Principal</div>
    <a href="../index.php" class="nav-link"><span class="nav-icon">⊞</span> Dashboard</a>
    <a href="../utilisateurs/index.php" class="nav-link"><span class="nav-icon">👥</span> Utilisateurs</a>
    <a href="../kyc/index.php" class="nav-link"><span class="nav-icon">🪪</span> KYC</a>
    <a href="../comptes/index.php" class="nav-link"><span class="nav-icon">🏦</span> Comptes</a>
    <a href="../cartes/index.php" class="nav-link"><span class="nav-icon">💳</span> Cartes</a>
    <a href="../transactions/index.php" class="nav-link"><span class="nav-icon">≡</span> Transactions</a>
    <a href="../virements/index.php" class="nav-link"><span class="nav-icon">⇄</span> Virements</a>
    <a href="../credits/index.php" class="nav-link"><span class="nav-icon">📋</span> Crédits</a>
    <a href="../assurances/index.php" class="nav-link"><span class="nav-icon">🛡️</span> Assurances</a>
    <div class="nav-section-label">Contrôle</div>
    <a href="../fraude/index.php" class="nav-link"><span class="nav-icon">🚨</span> Fraude</a>
    <a href="../support/index.php" class="nav-link"><span class="nav-icon">❓</span> Support</a>
    <div class="nav-section-label">Équipe & Config</div>
    <a href="../equipe/index.php" class="nav-link"><span class="nav-icon">👤</span> Équipe</a>
    <a href="../notifications/index.php" class="nav-link"><span class="nav-icon">🔔</span> Notifications</a>
    <a href="../logs/index.php" class="nav-link"><span class="nav-icon">📜</span> Audit Logs</a>
    <a href="../parametres/index.php" class="nav-link active"><span class="nav-icon">⚙</span> Paramètres</a>

  </nav>
  <div class="sidebar-footer"><a href="../../authentification/api/logout.php" class="nav-link" onclick="return confirm('Se déconnecter ?')"><span>🔒</span> Déconnexion</a></div>
</aside>
<header class="admin-topbar">
  <div style="display:flex;align-items:center;gap:1rem"><button class="sidebar-toggle" id="sidebarToggle">☰</button><h1 style="font-size:1rem;font-weight:700;color:var(--dark,#1e293b)"><?php echo $title??'';?></h1></div>
  <div style="display:flex;align-items:center;gap:.75rem"><span style="font-size:.8rem;color:var(--gray500,#6b7280)"><?php echo htmlspecialchars(($adm['first_name']??''),ENT_QUOTES,'UTF-8');?></span></div>
</header>
<main class="admin-main">
<?php
$title='Paramètres';
require_once __DIR__.'/../../backend/helpers.php';
$csrf    = Security::csrfToken();
$success = $error = '';

// Valeurs par défaut (config.php)
$settings = [
    'bank_name'            => GTB_NAME,
    'bank_bic'             => GTB_BIC,
    'bank_currency'        => GTB_CURRENCY,
    'transfer_limit_daily' => TRANSFER_LIMIT_DAILY,
    'transfer_limit_monthly'=> TRANSFER_LIMIT_MONTHLY,
    'transfer_limit_instant'=> TRANSFER_LIMIT_INSTANT,
    'transfer_fee_sepa'    => TRANSFER_FEE_SEPA,
    'transfer_fee_instant' => TRANSFER_FEE_INSTANT,
    'transfer_fee_intl'    => TRANSFER_FEE_INTERNATIONAL,
    'overdraft_default'    => OVERDRAFT_DEFAULT,
    'otp_lifetime'         => OTP_LIFETIME,
    'login_max_attempts'   => LOGIN_MAX_ATTEMPTS,
    'login_lock_minutes'   => LOGIN_LOCK_MINUTES,
    'mail_from'            => MAIL_FROM,
    'mail_support'         => MAIL_SUPPORT,
];

// Stats globales
$nb_users    = (int)DB::scalar("SELECT COUNT(*) FROM users WHERE is_active=1");
$nb_comptes  = (int)DB::scalar("SELECT COUNT(*) FROM comptes WHERE statut='actif'");
$nb_tx       = (int)DB::scalar("SELECT COUNT(*) FROM transactions");
$solde_total = (float)DB::scalar("SELECT COALESCE(SUM(solde),0) FROM comptes WHERE statut='actif'");
$nb_tickets  = (int)DB::scalar("SELECT COUNT(*) FROM tickets WHERE statut='ouvert'");

if ($_SERVER['REQUEST_METHOD']==='POST' && Security::csrfCheck($_POST['_csrf']??'')) {
    // Changement de mot de passe admin
    $action = $_POST['_action']??'';
    if ($action==='password') {
        $old = $_POST['old_password']??'';
        $new = $_POST['new_password']??'';
        $con = $_POST['confirm_password']??'';
        $hash = DB::scalar("SELECT password_hash FROM admins WHERE id=:id",['id'=>$currentAdmin['id']]);
        if (!password_verify($old, $hash)) { $error='Mot de passe actuel incorrect.'; }
        elseif (strlen($new)<8) { $error='Nouveau mot de passe trop court (8 min).'; }
        elseif ($new!==$con) { $error='Les mots de passe ne correspondent pas.'; }
        else {
            DB::update("UPDATE admins SET password_hash=:h WHERE id=:id",['h'=>password_hash($new,PASSWORD_ARGON2ID,['memory_cost'=>65536,'time_cost'=>4,'threads'=>2]),'id'=>$currentAdmin['id']]);
            $success='Mot de passe mis à jour.';
        }
    }
}
?>

<div class="page-header">
  <div class="page-header-top">
    <div><h1 class="page-title">Paramètres système</h1><p class="page-sub">Configuration de la plateforme Global Trust Bank</p></div>
  </div>
</div>

<?php if($success):?><div style="background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.2);border-radius:10px;padding:.85rem 1rem;margin-bottom:1.25rem;font-size:.82rem;color:var(--success)">✓ <?=htmlspecialchars($success)?></div><?php endif;?>
<?php if($error):?><div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);border-radius:10px;padding:.85rem 1rem;margin-bottom:1.25rem;font-size:.82rem;color:var(--danger)">✕ <?=htmlspecialchars($error)?></div><?php endif;?>

<!-- KPIs Système -->
<div class="admin-kpi-grid" style="margin-bottom:1.5rem">
  <div class="kpi-card"><div class="kpi-icon" style="background:rgba(59,130,246,.1);color:var(--accent)">👥</div><div><div class="kpi-label">Clients actifs</div><div class="kpi-value"><?=$nb_users?></div></div></div>
  <div class="kpi-card"><div class="kpi-icon" style="background:rgba(16,185,129,.1);color:var(--success)">🏦</div><div><div class="kpi-label">Comptes actifs</div><div class="kpi-value"><?=$nb_comptes?></div></div></div>
  <div class="kpi-card"><div class="kpi-icon" style="background:rgba(245,158,11,.12);color:#B45309">💰</div><div><div class="kpi-label">Encours total</div><div class="kpi-value"><?=number_format($solde_total,0,',',' ').' €'?></div></div></div>
  <div class="kpi-card"><div class="kpi-icon" style="background:rgba(239,68,68,.1);color:var(--danger)">🎫</div><div><div class="kpi-label">Tickets ouverts</div><div class="kpi-value"><?=$nb_tickets?></div></div></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem">

  <!-- Infos banque -->
  <div class="admin-card">
    <div class="card-header"><div class="card-title">🏦 Informations banque</div></div>
    <table style="width:100%;font-size:.82rem;border-collapse:collapse">
      <?php foreach([
        'Nom'      =>$settings['bank_name'],
        'BIC'      =>$settings['bank_bic'],
        'Devise'   =>$settings['bank_currency'],
        'Env.'     =>GTB_ENV,
      ] as $k=>$v):?>
      <tr style="border-bottom:1px solid var(--gray100)">
        <td style="padding:.65rem 0;color:var(--gray500);width:120px"><?=$k?></td>
        <td style="padding:.65rem 0;font-weight:600;color:var(--gray900);font-family:'JetBrains Mono',monospace;font-size:.78rem"><?=htmlspecialchars($v)?></td>
      </tr>
      <?php endforeach;?>
    </table>
  </div>

  <!-- Plafonds virements -->
  <div class="admin-card">
    <div class="card-header"><div class="card-title">⇄ Plafonds & Frais virements</div></div>
    <table style="width:100%;font-size:.82rem;border-collapse:collapse">
      <?php foreach([
        'Plafond journalier'  => number_format($settings['transfer_limit_daily'],0,',',' ').' €',
        'Plafond mensuel'     => number_format($settings['transfer_limit_monthly'],0,',',' ').' €',
        'Plafond instantané'  => number_format($settings['transfer_limit_instant'],0,',',' ').' €',
        'Frais SEPA'          => $settings['transfer_fee_sepa'].' €',
        'Frais instantané'    => $settings['transfer_fee_instant'].' €',
        'Frais international' => $settings['transfer_fee_intl'].' €',
        'Découvert défaut'    => $settings['overdraft_default'].' €',
      ] as $k=>$v):?>
      <tr style="border-bottom:1px solid var(--gray100)">
        <td style="padding:.65rem 0;color:var(--gray500);width:160px"><?=$k?></td>
        <td style="padding:.65rem 0;font-weight:600;color:var(--gray900)"><?=$v?></td>
      </tr>
      <?php endforeach;?>
    </table>
  </div>

  <!-- Sécurité -->
  <div class="admin-card">
    <div class="card-header"><div class="card-title">🔒 Sécurité & Sessions</div></div>
    <table style="width:100%;font-size:.82rem;border-collapse:collapse">
      <?php foreach([
        'Durée OTP'          => ($settings['otp_lifetime']/60).' min',
        'Tentatives max'     => $settings['login_max_attempts'],
        'Blocage connexion'  => $settings['login_lock_minutes'].' min',
        'Session inactive'   => (SESSION_LIFETIME/3600).'h',
      ] as $k=>$v):?>
      <tr style="border-bottom:1px solid var(--gray100)">
        <td style="padding:.65rem 0;color:var(--gray500);width:160px"><?=$k?></td>
        <td style="padding:.65rem 0;font-weight:600;color:var(--gray900)"><?=$v?></td>
      </tr>
      <?php endforeach;?>
    </table>
  </div>

  <!-- Email -->
  <div class="admin-card">
    <div class="card-header"><div class="card-title">📧 Configuration email</div></div>
    <table style="width:100%;font-size:.82rem;border-collapse:collapse">
      <?php foreach([
        'Expéditeur' => $settings['mail_from'],
        'Support'    => $settings['mail_support'],
        'Fournisseur'=> 'Brevo API',
      ] as $k=>$v):?>
      <tr style="border-bottom:1px solid var(--gray100)">
        <td style="padding:.65rem 0;color:var(--gray500);width:120px"><?=$k?></td>
        <td style="padding:.65rem 0;font-weight:600;color:var(--gray900);font-size:.78rem"><?=htmlspecialchars($v)?></td>
      </tr>
      <?php endforeach;?>
    </table>
  </div>

</div>

<!-- Changer mot de passe -->
<div class="admin-card" style="margin-top:1.25rem;max-width:480px">
  <div class="card-header"><div class="card-title">🔑 Changer mon mot de passe</div></div>
  <form method="POST">
    <input type="hidden" name="_csrf" value="<?=$csrf?>">
    <input type="hidden" name="_action" value="password">
    <div class="form-group"><label class="form-label">Mot de passe actuel</label><input class="form-input" type="password" name="old_password" required></div>
    <div class="form-group"><label class="form-label">Nouveau mot de passe</label><input class="form-input" type="password" name="new_password" required minlength="8"></div>
    <div class="form-group"><label class="form-label">Confirmer</label><input class="form-input" type="password" name="confirm_password" required minlength="8"></div>
    <button type="submit" class="btn btn-primary">Mettre à jour</button>
  </form>
</div>

<!-- ═══════════════════════════════════════════════════════
     CRÉER UN CLIENT
════════════════════════════════════════════════════════ -->
<div class="admin-card" style="margin-top:2rem">
  <div class="card-header">
    <div>
      <div class="card-title">➕ Créer un compte client</div>
      <div class="card-sub">L'admin crée le profil manuellement — IBAN et numéro client générés automatiquement</div>
    </div>
  </div>
  <form id="createUserForm" autocomplete="off">
    <div style="display:grid;grid-template-columns:120px 1fr 1fr;gap:.6rem;margin-bottom:.6rem">
      <div class="form-group" style="margin:0">
        <label class="form-label">Civilité</label>
        <select class="form-select" id="cu_civility" name="civility">
          <option value="">—</option>
          <option value="M.">M.</option>
          <option value="Mme">Mme</option>
        </select>
      </div>
      <div class="form-group" style="margin:0">
        <label class="form-label">Prénom <span style="color:var(--danger)">*</span></label>
        <input class="form-input" type="text" id="cu_first" placeholder="Jean" required>
      </div>
      <div class="form-group" style="margin:0">
        <label class="form-label">Nom <span style="color:var(--danger)">*</span></label>
        <input class="form-input" type="text" id="cu_last" placeholder="Dupont" required>
      </div>
    </div>
    <div class="form-row" style="margin-bottom:.6rem">
      <div class="form-group" style="margin:0">
        <label class="form-label">Email <span style="color:var(--danger)">*</span></label>
        <input class="form-input" type="email" id="cu_email" placeholder="jean.dupont@mail.com" required>
      </div>
      <div class="form-group" style="margin:0">
        <label class="form-label">Téléphone</label>
        <input class="form-input" type="tel" id="cu_tel" placeholder="+33 6 00 00 00 00">
      </div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:.6rem;margin-bottom:.6rem">
      <div class="form-group" style="margin:0">
        <label class="form-label">Mot de passe temp. <span style="color:var(--danger)">*</span></label>
        <div style="display:flex;gap:.35rem">
          <input class="form-input" type="text" id="cu_pwd" placeholder="••••••••" required minlength="8" style="flex:1;min-width:0">
          <button type="button" onclick="genPwd()" class="btn btn-outline btn-sm" title="Générer" style="flex-shrink:0;padding:.5rem .7rem">🎲</button>
        </div>
      </div>
      <div class="form-group" style="margin:0">
        <label class="form-label">Type de compte</label>
        <select class="form-select" id="cu_plan">
          <option value="standard">Standard</option>
          <option value="premium">Premium</option>
          <option value="business">Business</option>
        </select>
      </div>
      <div class="form-group" style="margin:0">
        <label class="form-label">Devise</label>
        <select class="form-select" id="cu_devise">
          <option value="EUR">EUR €</option>
          <option value="USD">USD $</option>
          <option value="GBP">GBP £</option>
          <option value="CHF">CHF</option>
          <option value="MAD">MAD</option>
          <option value="XOF">XOF</option>
        </select>
      </div>
      <div class="form-group" style="margin:0">
        <label class="form-label">Langue</label>
        <select class="form-select" id="cu_langue">
          <option value="fr">Français</option>
          <option value="en">English</option>
          <option value="es">Español</option>
          <option value="de">Deutsch</option>
        </select>
      </div>
    </div>
    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem">
      <label style="display:flex;align-items:center;gap:.4rem;font-size:.8rem;color:var(--gray700);cursor:pointer">
        <input type="checkbox" id="cu_card" style="accent-color:var(--accent)"> Créer une carte bancaire
      </label>
    </div>
    <div id="cuResult" style="display:none;margin-bottom:1rem;padding:.85rem 1rem;border-radius:10px;font-size:.82rem;line-height:1.7"></div>
    <button type="submit" class="btn btn-primary" id="cuBtn">Créer le client</button>
  </form>
</div>

<!-- ═══════════════════════════════════════════════════════
     METTRE À JOUR UN ACCÈS CLIENT
════════════════════════════════════════════════════════ -->
<div class="admin-card" style="margin-top:2rem">
  <div class="card-header">
    <div>
      <div class="card-title">🛠 Mettre à jour un accès client</div>
      <div class="card-sub">Recherchez un client puis appliquez des actions directement sur son compte</div>
    </div>
  </div>

  <!-- Recherche client -->
  <div style="display:flex;gap:.5rem;margin-bottom:1rem">
    <div class="admin-search" style="flex:1;max-width:none">
      <span style="font-size:.9rem;color:var(--gray400)">🔍</span>
      <input type="text" id="clientSearch" placeholder="Nom, email ou numéro client..." oninput="searchClient(this.value)" autocomplete="off">
    </div>
    <button class="btn btn-outline btn-sm" onclick="clearClient()">✕ Effacer</button>
  </div>

  <!-- Résultats recherche -->
  <div id="clientResults" style="display:none;margin-bottom:1rem;background:var(--gray50);border:1px solid var(--gray200);border-radius:10px;overflow:hidden;max-height:220px;overflow-y:auto"></div>

  <!-- Fiche client sélectionné -->
  <div id="clientCard" style="display:none;margin-bottom:1.25rem">
    <div style="background:linear-gradient(135deg,var(--admin-bg),var(--admin-bg-mid));border-radius:12px;padding:1.1rem 1.25rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
      <div style="display:flex;align-items:center;gap:.85rem">
        <div id="cc_avatar" style="width:44px;height:44px;border-radius:10px;background:linear-gradient(135deg,var(--accent),var(--accent-deep));display:flex;align-items:center;justify-content:center;font-family:'Sora',sans-serif;font-weight:700;font-size:.9rem;color:white;flex-shrink:0"></div>
        <div>
          <div id="cc_name" style="font-family:'Sora',sans-serif;font-weight:700;font-size:.95rem;color:white"></div>
          <div id="cc_email" style="font-size:.72rem;color:rgba(255,255,255,.55);margin-top:.1rem"></div>
          <div id="cc_num" style="font-size:.68rem;color:rgba(255,255,255,.35);font-family:'JetBrains Mono',monospace;margin-top:.1rem"></div>
        </div>
      </div>
      <div style="display:flex;gap:1.5rem;flex-wrap:wrap">
        <div style="text-align:right">
          <div style="font-size:.62rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.2rem">Solde</div>
          <div id="cc_solde" style="font-family:'Sora',sans-serif;font-weight:800;font-size:1.15rem;color:#10B981"></div>
        </div>
        <div style="text-align:right">
          <div style="font-size:.62rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.2rem">IBAN</div>
          <div id="cc_iban" style="font-family:'JetBrains Mono',monospace;font-size:.72rem;color:rgba(255,255,255,.7)"></div>
        </div>
        <div style="text-align:right">
          <div style="font-size:.62rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.2rem">Statut</div>
          <div id="cc_status" style="font-size:.75rem;font-weight:700"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Panneau d'actions (masqué jusqu'à sélection client) -->
  <div id="actionPanel" style="display:none">
    <div id="actionMsg" style="display:none;margin-bottom:.85rem;padding:.7rem 1rem;border-radius:8px;font-size:.8rem"></div>

    <!-- Chips catégories -->
    <div class="chips-bar" id="actionChips">
      <span class="chip active" data-cat="virements">💸 Virements</span>
      <span class="chip" data-cat="certification">📊 Certification</span>
      <span class="chip" data-cat="compte">🏦 Compte</span>
      <span class="chip" data-cat="carte">💳 Carte</span>
      <span class="chip" data-cat="securite">🔒 Sécurité</span>
      <span class="chip" data-cat="kyc">🪪 KYC</span>
      <span class="chip" data-cat="comms">📨 Communication</span>
    </div>

    <!-- ── VIREMENTS ── -->
    <div class="action-section" data-cat="virements">
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem">
        <?php foreach([
          ['virement_entrant','Créditer','✅','Créditer le compte client','success'],
          ['virement_sortant','Débiter','⬆️','Débiter le compte client','danger'],
          ['remboursement','Remboursement','🔄','Crédit remboursement','info'],
        ] as [$act,$label,$ico,$sub,$style]):?>
        <div class="admin-card" style="padding:1rem">
          <div style="font-size:1.2rem;margin-bottom:.35rem"><?=$ico?></div>
          <div style="font-weight:700;font-size:.82rem;color:var(--gray900);margin-bottom:.15rem"><?=$label?></div>
          <div style="font-size:.72rem;color:var(--gray500);margin-bottom:.85rem"><?=$sub?></div>
          <div class="form-group"><label class="form-label">Montant (€)</label><input class="form-input form-input-sm" type="number" step="0.01" min="0.01" name="montant" data-action="<?=$act?>" placeholder="0.00"></div>
          <div class="form-group"><label class="form-label">Motif</label><input class="form-input form-input-sm" type="text" name="motif" data-action="<?=$act?>" placeholder="Motif..."></div>
          <div class="form-group"><label class="form-label">Référence (optionnel)</label><input class="form-input form-input-sm" type="text" name="ref" data-action="<?=$act?>" placeholder="Auto"></div>
          <div class="form-group"><label class="form-label">Date backdatée (optionnel)</label><input class="form-input form-input-sm" type="datetime-local" name="backdated_at" data-action="<?=$act?>"></div>
          <button class="btn btn-<?=$style?> btn-sm" style="width:100%" onclick="doAction('<?=$act?>')">Exécuter</button>
        </div>
        <?php endforeach;?>
      </div>
    </div>

    <!-- ── CERTIFICATION ── -->
    <div class="action-section" data-cat="certification" style="display:none">
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1rem">
        <?php foreach([
          ['cert_valider','Valider virement','✅','success'],
          ['cert_rejeter','Rejeter virement','❌','danger'],
          ['cert_reset','Remettre à 0%','🔁','outline'],
          ['cert_geler','Geler position','⏸️','outline'],
          ['cert_debloquer','Débloquer','▶️','outline'],
          ['cert_forcer_100','Forcer 100%','💯','primary'],
        ] as [$act,$label,$ico,$style]):?>
        <button class="btn btn-<?=$style?> btn-sm" onclick="doAction('<?=$act?>')"><?=$ico?> <?=$label?></button>
        <?php endforeach;?>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div class="admin-card" style="padding:1rem">
          <div style="font-weight:700;font-size:.82rem;margin-bottom:.85rem">⏸️ Geler à un pourcentage</div>
          <div class="form-group"><label class="form-label">Pourcentage</label><input class="form-input" type="number" min="0" max="99" name="pct" data-action="cert_bloquer" value="50"></div>
          <div class="form-group"><label class="form-label">Message client</label><input class="form-input" type="text" name="cert-msg" data-action="cert_bloquer" placeholder="Vérification en cours..."></div>
          <button class="btn btn-warning btn-sm" style="width:100%" onclick="doAction('cert_bloquer')">Geler ici</button>
        </div>
        <div class="admin-card" style="padding:1rem">
          <div style="font-weight:700;font-size:.82rem;margin-bottom:.85rem">⚡ Vitesse de progression</div>
          <div class="form-group"><label class="form-label">Vitesse</label>
            <select class="form-select" name="vitesse" data-action="cert_vitesse">
              <option value="slow">Lente</option>
              <option value="normal" selected>Normale</option>
              <option value="fast">Rapide</option>
            </select>
          </div>
          <div class="form-group"><label class="form-label">Message affiché</label><input class="form-input" type="text" name="cert-msg" data-action="cert_message" placeholder="Message..."></div>
          <div style="display:flex;gap:.5rem">
            <button class="btn btn-outline btn-sm" style="flex:1" onclick="doAction('cert_vitesse')">Changer vitesse</button>
            <button class="btn btn-outline btn-sm" style="flex:1" onclick="doAction('cert_message')">Maj message</button>
          </div>
        </div>
      </div>
      <div class="admin-card" style="padding:1rem;margin-top:1rem">
        <div style="font-weight:700;font-size:.82rem;margin-bottom:.85rem">🚦 Seuil d'arrêt virement (%)</div>
        <div style="display:flex;gap:.5rem;align-items:flex-end">
          <div class="form-group" style="margin:0;flex:1"><label class="form-label">Pourcentage (0 = désactivé)</label><input class="form-input" type="number" min="0" max="100" name="pct" data-action="stop_virement_pct" value="0"></div>
          <button class="btn btn-outline btn-sm" onclick="doAction('stop_virement_pct')">Appliquer</button>
        </div>
      </div>
    </div>

    <!-- ── COMPTE ── -->
    <div class="action-section" data-cat="compte" style="display:none">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div class="admin-card" style="padding:1rem">
          <div style="font-weight:700;font-size:.82rem;margin-bottom:.85rem">💰 Modifier le solde</div>
          <div class="form-group"><label class="form-label">Nouveau solde exact (€)</label><input class="form-input" type="number" step="0.01" name="montant" data-action="modifier_solde" placeholder="0.00"></div>
          <button class="btn btn-warning btn-sm" style="width:100%" onclick="doAction('modifier_solde')">Appliquer</button>
        </div>
        <div class="admin-card" style="padding:1rem">
          <div style="font-weight:700;font-size:.82rem;margin-bottom:.85rem">📉 Découvert autorisé</div>
          <div class="form-group"><label class="form-label">Montant (€)</label><input class="form-input" type="number" step="0.01" name="montant" data-action="modifier_decouvert" placeholder="500.00"></div>
          <button class="btn btn-outline btn-sm" style="width:100%" onclick="doAction('modifier_decouvert')">Modifier</button>
        </div>
        <div class="admin-card" style="padding:1rem">
          <div style="font-weight:700;font-size:.82rem;margin-bottom:.85rem">🏦 IBAN / BIC</div>
          <div class="form-group"><label class="form-label">IBAN</label><input class="form-input" type="text" name="iban" data-action="modifier_iban_bic" placeholder="FR76..."></div>
          <div class="form-group"><label class="form-label">BIC</label><input class="form-input" type="text" name="bic" data-action="modifier_iban_bic" placeholder="GTBXFRP1"></div>
          <button class="btn btn-outline btn-sm" style="width:100%" onclick="doAction('modifier_iban_bic')">Mettre à jour</button>
        </div>
        <div class="admin-card" style="padding:1rem">
          <div style="font-weight:700;font-size:.82rem;margin-bottom:.85rem">🔄 Type de compte</div>
          <div class="form-group"><label class="form-label">Type</label>
            <select class="form-select" name="type-compte" data-action="changer_type_compte">
              <option value="standard">Standard</option>
              <option value="premium">Premium</option>
              <option value="business">Business</option>
            </select>
          </div>
          <button class="btn btn-outline btn-sm" style="width:100%" onclick="doAction('changer_type_compte')">Changer</button>
        </div>
        <div class="admin-card" style="padding:1rem">
          <div style="font-weight:700;font-size:.82rem;margin-bottom:.85rem">🚫 Bloquer l'accès</div>
          <div class="form-group"><label class="form-label">Motif</label><input class="form-input" type="text" name="motif" data-action="bloquer_acces" placeholder="Activité suspecte"></div>
          <div class="form-group"><label class="form-label">Type</label>
            <select class="form-select" name="block-type" data-action="bloquer_acces">
              <option value="permanent">Permanent</option>
              <option value="temporary">Temporaire</option>
            </select>
          </div>
          <div class="form-group"><label class="form-label">Jusqu'au (si temporaire)</label><input class="form-input" type="datetime-local" name="block-until" data-action="bloquer_acces"></div>
          <div style="display:flex;gap:.5rem">
            <button class="btn btn-danger btn-sm" style="flex:1" onclick="doAction('bloquer_acces')">Bloquer</button>
            <button class="btn btn-success btn-sm" style="flex:1" onclick="doAction('debloquer_acces')">Débloquer</button>
          </div>
        </div>
        <div class="admin-card" style="padding:1rem">
          <div style="font-weight:700;font-size:.82rem;margin-bottom:.85rem">⚖️ Plafonds</div>
          <div class="form-group"><label class="form-label">Retrait (€)</label><input class="form-input" type="number" name="montant" data-action="plafond_retrait" placeholder="10000"></div>
          <button class="btn btn-outline btn-sm" style="width:100%;margin-bottom:.5rem" onclick="doAction('plafond_retrait')">Maj retrait</button>
          <div class="form-group"><label class="form-label">Virement (€)</label><input class="form-input" type="number" name="montant" data-action="plafond_virement" placeholder="50000"></div>
          <button class="btn btn-outline btn-sm" style="width:100%;margin-bottom:.5rem" onclick="doAction('plafond_virement')">Maj virement</button>
          <div class="form-group"><label class="form-label">Paiement (€)</label><input class="form-input" type="number" name="montant" data-action="plafond_paiement" placeholder="5000"></div>
          <button class="btn btn-outline btn-sm" style="width:100%" onclick="doAction('plafond_paiement')">Maj paiement</button>
        </div>
      </div>
      <div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-top:1rem">
        <button class="btn btn-outline btn-sm" onclick="doAction('suspendre_compte')">⏸ Suspendre</button>
        <button class="btn btn-danger btn-sm" onclick="confirmDlg('Fermer définitivement ce compte ?',()=>doAction('fermer_compte'))">🗑 Fermer compte</button>
      </div>
    </div>

    <!-- ── CARTE ── -->
    <div class="action-section" data-cat="carte" style="display:none">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div class="admin-card" style="padding:1rem">
          <div style="font-weight:700;font-size:.82rem;margin-bottom:.85rem">💳 Statut carte</div>
          <div style="display:flex;gap:.5rem;flex-wrap:wrap">
            <button class="btn btn-danger btn-sm" onclick="doAction('bloquer_carte')">🔒 Bloquer</button>
            <button class="btn btn-success btn-sm" onclick="doAction('debloquer_carte')">🔓 Débloquer</button>
            <button class="btn btn-outline btn-sm" onclick="doAction('renouveler_carte')">🔄 Renouveler</button>
            <button class="btn btn-outline btn-sm" onclick="doAction('toggle_paiement_en_ligne')">🌐 Toggle en ligne</button>
            <button class="btn btn-outline btn-sm" onclick="doAction('toggle_paiement_etranger')">✈️ Toggle étranger</button>
          </div>
        </div>
        <div class="admin-card" style="padding:1rem">
          <div style="font-weight:700;font-size:.82rem;margin-bottom:.85rem">📝 Infos carte</div>
          <div class="form-group"><label class="form-label">Numéro carte</label><input class="form-input" type="text" name="card-num" data-action="modifier_infos_carte" placeholder="1234 5678 9012 3456"></div>
          <div class="form-group"><label class="form-label">CVV</label><input class="form-input" type="text" name="cvv" data-action="modifier_infos_carte" placeholder="123" maxlength="4"></div>
          <div class="form-group"><label class="form-label">Expiration (MM/YYYY)</label><input class="form-input" type="month" name="expire" data-action="modifier_infos_carte"></div>
          <button class="btn btn-outline btn-sm" style="width:100%" onclick="doAction('modifier_infos_carte')">Mettre à jour</button>
        </div>
        <div class="admin-card" style="padding:1rem">
          <div style="font-weight:700;font-size:.82rem;margin-bottom:.85rem">💰 Plafond carte</div>
          <div class="form-group"><label class="form-label">Montant (€)</label><input class="form-input" type="number" step="0.01" name="montant" data-action="modifier_plafond_carte" placeholder="3000"></div>
          <button class="btn btn-outline btn-sm" style="width:100%" onclick="doAction('modifier_plafond_carte')">Modifier</button>
        </div>
      </div>
    </div>

    <!-- ── SÉCURITÉ ── -->
    <div class="action-section" data-cat="securite" style="display:none">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div class="admin-card" style="padding:1rem">
          <div style="font-weight:700;font-size:.82rem;margin-bottom:.85rem">🔑 Réinitialiser le mot de passe</div>
          <div class="form-group"><label class="form-label">Nouveau mot de passe temporaire</label>
            <div style="display:flex;gap:.35rem">
              <input class="form-input" type="text" id="resetPwdInput" name="reset_pwd_val" placeholder="Générer..." style="flex:1">
              <button type="button" onclick="genResetPwd()" class="btn btn-outline btn-sm">🎲</button>
            </div>
          </div>
          <div class="form-group"><label class="form-label">Message email (optionnel)</label><textarea class="form-textarea" name="notif_msg" data-action="reset_password" rows="2" placeholder="Votre mot de passe a été réinitialisé..."></textarea></div>
          <div style="display:flex;gap:.5rem;align-items:center">
            <input type="checkbox" id="sendEmailPwd" style="accent-color:var(--accent)"> <label for="sendEmailPwd" style="font-size:.78rem">Envoyer par email</label>
          </div>
          <button class="btn btn-warning btn-sm" style="width:100%;margin-top:.75rem" onclick="doAction('reset_password')">Réinitialiser</button>
        </div>
        <div class="admin-card" style="padding:1rem">
          <div style="font-weight:700;font-size:.82rem;margin-bottom:.85rem">🛡️ Actions sécurité</div>
          <div style="display:flex;flex-direction:column;gap:.5rem">
            <button class="btn btn-outline btn-sm" onclick="doAction('toggle_2fa')">🔐 Activer / Désactiver 2FA</button>
            <button class="btn btn-danger btn-sm" onclick="doAction('forcer_deconnexion')">⏏ Révoquer toutes sessions</button>
          </div>
        </div>
      </div>
    </div>

    <!-- ── KYC ── -->
    <div class="action-section" data-cat="kyc" style="display:none">
      <div style="display:flex;gap:.75rem;flex-wrap:wrap">
        <button class="btn btn-success btn-sm" onclick="doAction('valider_kyc')">✅ Valider KYC</button>
        <button class="btn btn-danger btn-sm" onclick="doAction('refuser_kyc')">❌ Refuser KYC</button>
        <button class="btn btn-outline btn-sm" onclick="doAction('demander_documents')">📄 Demander documents</button>
      </div>
      <div class="admin-card" style="padding:1rem;margin-top:1rem;max-width:340px">
        <div style="font-weight:700;font-size:.82rem;margin-bottom:.85rem">Changer statut KYC</div>
        <div class="form-group"><label class="form-label">Statut</label>
          <select class="form-select" name="kyc-status" data-action="changer_statut_kyc">
            <option value="pending">En attente</option>
            <option value="verified">Vérifié</option>
            <option value="rejected">Refusé</option>
          </select>
        </div>
        <button class="btn btn-primary btn-sm" style="width:100%" onclick="doAction('changer_statut_kyc')">Appliquer</button>
      </div>
    </div>

    <!-- ── COMMUNICATION ── -->
    <div class="action-section" data-cat="comms" style="display:none">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div class="admin-card" style="padding:1rem">
          <div style="font-weight:700;font-size:.82rem;margin-bottom:.85rem">🔔 Notification in-app</div>
          <div class="form-group"><label class="form-label">Titre</label><input class="form-input" type="text" name="notif-t" data-action="envoyer_notification" placeholder="Titre..."></div>
          <div class="form-group"><label class="form-label">Message</label><textarea class="form-textarea" name="notif-m" data-action="envoyer_notification" rows="3" placeholder="Message..."></textarea></div>
          <div class="form-group"><label class="form-label">Type</label>
            <select class="form-select" name="notif-type" data-action="envoyer_notification">
              <option value="info">Info</option>
              <option value="success">Succès</option>
              <option value="warning">Avertissement</option>
              <option value="danger">Alerte</option>
            </select>
          </div>
          <button class="btn btn-primary btn-sm" style="width:100%" onclick="doAction('envoyer_notification')">Envoyer notification</button>
        </div>
        <div class="admin-card" style="padding:1rem">
          <div style="font-weight:700;font-size:.82rem;margin-bottom:.85rem">📧 Message par email</div>
          <div class="form-group"><label class="form-label">Sujet</label><input class="form-input" type="text" id="emailSubject" placeholder="Sujet de l'email..."></div>
          <div class="form-group"><label class="form-label">Message</label><textarea class="form-textarea" name="message" data-action="envoyer_message" rows="4" placeholder="Corps du message..."></textarea></div>
          <button class="btn btn-primary btn-sm" style="width:100%" onclick="doAction('envoyer_message',{send_email:true,notif_title:document.getElementById('emailSubject').value})">Envoyer email</button>
        </div>
        <div class="admin-card" style="padding:1rem">
          <div style="font-weight:700;font-size:.82rem;margin-bottom:.85rem">🌍 Langue & Couleur interface</div>
          <div class="form-group"><label class="form-label">Langue</label>
            <select class="form-select" name="langue" data-action="changer_langue">
              <option value="fr">Français</option><option value="en">English</option>
              <option value="es">Español</option><option value="de">Deutsch</option>
            </select>
          </div>
          <button class="btn btn-outline btn-sm" style="width:100%;margin-bottom:.5rem" onclick="doAction('changer_langue')">Changer langue</button>
          <div class="form-group" style="margin-top:.5rem"><label class="form-label">Couleur interface</label>
            <select class="form-select" name="couleur" data-action="changer_couleur">
              <option value="default">Défaut</option><option value="dark">Dark</option>
              <option value="blue">Bleu</option><option value="green">Vert</option>
            </select>
          </div>
          <button class="btn btn-outline btn-sm" style="width:100%" onclick="doAction('changer_couleur')">Changer couleur</button>
        </div>
      </div>
    </div>

  </div><!-- /actionPanel -->
</div>

</main></div><div class="toast-container" id="toastContainer"></div><script>
(function(){'use strict';
const sb=document.getElementById('sidebar'),tgl=document.getElementById('sidebarToggle'),ov=document.getElementById('sidebarOverlay');
function openSb(){sb?.classList.add('open');ov?.classList.add('show');}
function closeSb(){sb?.classList.remove('open');ov?.classList.remove('show');}
tgl?.addEventListener('click',()=>sb?.classList.contains('open')?closeSb():openSb());
ov?.addEventListener('click',closeSb);
window.showToast=function(m,t){const c=document.getElementById('toastContainer');if(!c)return;const ic={success:'✓',error:'✕',info:'ℹ',warning:'⚠'};const x=document.createElement('div');x.className='toast '+(t||'success');x.innerHTML=`<span class="toast-icon">${ic[t||'success']||'•'}</span><span>${m}</span>`;c.appendChild(x);requestAnimationFrame(()=>setTimeout(()=>x.classList.add('show'),25));setTimeout(()=>{x.classList.remove('show');setTimeout(()=>x.remove(),400);},3600);};
window.openModal=function(id){document.getElementById(id)?.classList.add('open');};
window.closeModal=function(id){document.getElementById(id)?.classList.remove('open');};
window.confirmDlg=function(msg,onOk){const d=document.createElement('div');d.style.cssText='position:fixed;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;z-index:9000;padding:1rem';d.innerHTML=`<div style="background:#fff;border-radius:16px;width:100%;max-width:360px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.2)"><div style="padding:1.5rem;text-align:center"><div style="font-size:1.8rem;margin-bottom:.5rem">⚠️</div><p style="font-size:.85rem;color:#374151">${msg}</p></div><div style="display:flex;border-top:1px solid #e5e7eb"><button style="flex:1;padding:.75rem;border:none;background:none;cursor:pointer;font-weight:600" id="_cc">Annuler</button><button style="flex:1;padding:.75rem;border:none;background:#dc2626;color:#fff;cursor:pointer;font-weight:700" id="_co">Confirmer</button></div></div>`;document.body.appendChild(d);d.querySelector('#_cc').onclick=()=>d.remove();d.querySelector('#_co').onclick=()=>{d.remove();onOk?.();};};
document.querySelectorAll('.modal-overlay,.chip[data-filter]').forEach(el=>{if(el.classList.contains('modal-overlay'))el.addEventListener('click',e=>{if(e.target===el)el.classList.remove('open');});});
document.querySelectorAll('[data-filter]').forEach(c=>{c.addEventListener('click',()=>{c.closest('.chips-bar')?.querySelectorAll('[data-filter]').forEach(x=>x.classList.remove('active'));c.classList.add('active');});});

// ─── CRÉER UN CLIENT ───────────────────────────────────
function genPwd(){const c='ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$';let p='';for(let i=0;i<12;i++)p+=c[Math.floor(Math.random()*c.length)];document.getElementById('cu_pwd').value=p;}
document.getElementById('createUserForm')?.addEventListener('submit',async function(e){
  e.preventDefault();
  const btn=document.getElementById('cuBtn');
  const res=document.getElementById('cuResult');
  btn.disabled=true;btn.textContent='Création en cours...';
  const body={
    first_name:document.getElementById('cu_first').value.trim(),
    last_name:document.getElementById('cu_last').value.trim(),
    email:document.getElementById('cu_email').value.trim(),
    telephone:document.getElementById('cu_tel').value.trim(),
    civility:document.getElementById('cu_civility').value,
    password:document.getElementById('cu_pwd').value,
    plan:document.getElementById('cu_plan').value,
    devise:document.getElementById('cu_devise').value,
    langue:document.getElementById('cu_langue').value,
    with_card:document.getElementById('cu_card').checked,
  };
  try{
    const r=await fetch('api/create_user.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});
    const d=await r.json();
    res.style.display='block';
    if(d.success){
      res.style.cssText='display:block;background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.2);border-radius:10px;padding:.85rem 1rem;margin-bottom:1rem;font-size:.82rem;color:#065f46;line-height:1.8';
      res.innerHTML=`<strong>✓ ${d.message}</strong><br>
        N° client : <code>${d.client_number}</code><br>
        IBAN : <code>${d.iban}</code> &nbsp; BIC : <code>${d.bic}</code><br>
        Mot de passe temporaire : <code>${d.temp_password}</code>`;
      showToast(d.message,'success');
      e.target.reset();
    }else{
      res.style.cssText='display:block;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);border-radius:10px;padding:.85rem 1rem;margin-bottom:1rem;font-size:.82rem;color:var(--danger)';
      res.textContent='✕ '+d.message;
      showToast(d.message,'error');
    }
  }catch(err){showToast('Erreur réseau','error');}
  btn.disabled=false;btn.textContent='Créer le client';
});

// ─── ACCÈS CLIENT ─────────────────────────────────────
let selectedClientId=null;
let searchTimer=null;
function searchClient(q){
  clearTimeout(searchTimer);
  const res=document.getElementById('clientResults');
  if(q.length<2){res.style.display='none';return;}
  searchTimer=setTimeout(async()=>{
    const r=await fetch('../acces-client/api/clients.php?q='+encodeURIComponent(q));
    const d=await r.json();
    if(!d.success||!d.clients.length){res.style.display='none';return;}
    res.style.display='block';
    res.innerHTML=d.clients.map(c=>`
      <div onclick="selectClient(${JSON.stringify(c).replace(/"/g,'&quot;')})"
        style="display:flex;align-items:center;gap:.75rem;padding:.65rem 1rem;cursor:pointer;border-bottom:1px solid var(--gray100);transition:.15s"
        onmouseover="this.style.background='var(--gray50)'" onmouseout="this.style.background=''">
        <div style="width:36px;height:36px;border-radius:8px;background:linear-gradient(135deg,var(--accent),var(--accent-deep));display:flex;align-items:center;justify-content:center;font-family:'Sora',sans-serif;font-weight:700;font-size:.72rem;color:white;flex-shrink:0">${c.initials}</div>
        <div style="flex:1;min-width:0">
          <div style="font-weight:600;font-size:.82rem;color:var(--gray900)">${c.name}</div>
          <div style="font-size:.7rem;color:var(--gray500)">${c.email} &nbsp;·&nbsp; ${c.client_number||'—'}</div>
        </div>
        <div style="text-align:right;flex-shrink:0">
          <div style="font-family:'Sora',sans-serif;font-weight:700;font-size:.82rem;color:${c.solde>=0?'var(--success)':'var(--danger)'}">${Number(c.solde).toLocaleString('fr-FR',{minimumFractionDigits:2})} ${c.devise}</div>
          <span style="font-size:.62rem;font-weight:600;padding:.1rem .4rem;border-radius:999px;background:${c.status==='active'?'rgba(16,185,129,.1)':'rgba(239,68,68,.1)'};color:${c.status==='active'?'var(--success)':'var(--danger)'}">${c.status}</span>
        </div>
      </div>`).join('');
  },280);
}
function selectClient(c){
  selectedClientId=c.id;
  document.getElementById('clientResults').style.display='none';
  document.getElementById('clientSearch').value=c.name+' — '+c.email;
  // Fiche
  document.getElementById('cc_avatar').textContent=c.initials;
  document.getElementById('cc_name').textContent=c.name;
  document.getElementById('cc_email').textContent=c.email;
  document.getElementById('cc_num').textContent=c.client_number||'';
  document.getElementById('cc_solde').textContent=Number(c.solde).toLocaleString('fr-FR',{minimumFractionDigits:2})+' '+c.devise;
  document.getElementById('cc_iban').textContent=c.iban_fmt||c.iban_raw||'—';
  const statusEl=document.getElementById('cc_status');
  statusEl.textContent=c.status;
  statusEl.style.color=c.status==='active'?'#10B981':'#EF4444';
  document.getElementById('clientCard').style.display='block';
  document.getElementById('actionPanel').style.display='block';
}
function clearClient(){
  selectedClientId=null;
  document.getElementById('clientSearch').value='';
  document.getElementById('clientResults').style.display='none';
  document.getElementById('clientCard').style.display='none';
  document.getElementById('actionPanel').style.display='none';
}
// Chips catégories
document.getElementById('actionChips')?.querySelectorAll('.chip').forEach(chip=>{
  chip.addEventListener('click',()=>{
    document.getElementById('actionChips').querySelectorAll('.chip').forEach(c=>c.classList.remove('active'));
    chip.classList.add('active');
    const cat=chip.dataset.cat;
    document.querySelectorAll('.action-section').forEach(s=>s.style.display=s.dataset.cat===cat?'':'none');
  });
});
function genResetPwd(){const c='ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$';let p='';for(let i=0;i<10;i++)p+=c[Math.floor(Math.random()*c.length)];document.getElementById('resetPwdInput').value=p;}
async function doAction(action,extra={}){
  if(!selectedClientId){showToast('Aucun client sélectionné','error');return;}
  // Collect form_data for this action
  const fd={};
  document.querySelectorAll(`[data-action="${action}"]`).forEach(el=>{
    if(el.name)fd[el.name]=el.value;
  });
  // Special: reset_password needs the pwd field
  if(action==='reset_password'){
    fd.password=document.getElementById('resetPwdInput').value;
    fd.notif_msg=fd.notif_msg||'';
  }
  const body={
    action,
    client_id:selectedClientId,
    form_data:fd,
    send_email:!!extra.send_email,
    notif_title:extra.notif_title||'',
    ...extra
  };
  const msgEl=document.getElementById('actionMsg');
  try{
    const r=await fetch('../acces-client/api/action.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});
    const d=await r.json();
    msgEl.style.display='block';
    if(d.success){
      msgEl.style.cssText='display:block;margin-bottom:.85rem;padding:.7rem 1rem;border-radius:8px;font-size:.8rem;background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.2);color:#065f46';
      msgEl.textContent='✓ '+d.message;
      showToast(d.message,'success');
    }else{
      msgEl.style.cssText='display:block;margin-bottom:.85rem;padding:.7rem 1rem;border-radius:8px;font-size:.8rem;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:var(--danger)';
      msgEl.textContent='✕ '+(d.message||'Erreur');
      showToast(d.message||'Erreur','error');
    }
  }catch(err){showToast('Erreur réseau','error');}
}
})();
</script></body></html>
