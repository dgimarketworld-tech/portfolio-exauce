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
    <a href="../transactions/index.php" class="nav-link active"><span class="nav-icon">≡</span> Transactions</a>
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
    <a href="../parametres/index.php" class="nav-link"><span class="nav-icon">⚙</span> Paramètres</a>

  </nav>
  <div class="sidebar-footer"><a href="../../authentification/api/logout.php" class="nav-link" onclick="return confirm('Se déconnecter ?')"><span>🔒</span> Déconnexion</a></div>
</aside>
<header class="admin-topbar">
  <div style="display:flex;align-items:center;gap:1rem"><button class="sidebar-toggle" id="sidebarToggle">☰</button><h1 style="font-size:1rem;font-weight:700;color:var(--dark,#1e293b)"><?php echo $title??'';?></h1></div>
  <div style="display:flex;align-items:center;gap:.75rem"><span style="font-size:.8rem;color:var(--gray500,#6b7280)"><?php echo htmlspecialchars(($adm['first_name']??''),ENT_QUOTES,'UTF-8');?></span></div>
</header>
<main class="admin-main">
<?php
$title='Transactions';
$per=30; $page=max(1,(int)($_GET['page']??1));
$type_f=$_GET['type']??'';
$where='1=1'; $params=[];
if($type_f&&in_array($type_f,['depot','retrait','virement_in','virement_out','frais'])){$where.=" AND t.type=:type";$params['type']=$type_f;}
$total=(int)DB::scalar("SELECT COUNT(*) FROM transactions t WHERE $where",$params);
$pg=pagination($total,$per,$page);
$params['l']=$per; $params['o']=$pg['offset'];
$txs=DB::all("SELECT t.*,c.numero,u.first_name,u.last_name FROM transactions t JOIN comptes c ON t.compte_id=c.id JOIN users u ON c.user_id=u.id WHERE $where ORDER BY t.cree_le DESC LIMIT :l OFFSET :o",$params);
?>
<div style="display:flex;gap:.5rem;margin-bottom:1.25rem;flex-wrap:wrap;align-items:center">
  <div class="chips-bar">
    <a href="?type=" class="chip <?php echo !$type_f?'active':'';?>">Tous</a>
    <a href="?type=depot" class="chip <?php echo $type_f==='depot'?'active':'';?>">Dépôts</a>
    <a href="?type=virement_out" class="chip <?php echo $type_f==='virement_out'?'active':'';?>">Virements émis</a>
    <a href="?type=virement_in" class="chip <?php echo $type_f==='virement_in'?'active':'';?>">Virements reçus</a>
    <a href="?type=retrait" class="chip <?php echo $type_f==='retrait'?'active':'';?>">Retraits</a>
  </div>
  <div style="margin-left:auto;font-size:.82rem;color:var(--gray400)"><?php echo number_format($total);?> transaction(s)</div>
</div>
<div class="admin-card">
  <table class="admin-table"><thead><tr><th>Référence</th><th>Client</th><th>Type</th><th>Compte</th><th style="text-align:right">Montant</th><th>Statut</th><th>Date</th></tr></thead>
  <tbody>
  <?php foreach($txs as $tx): $in=strpos($tx['type'],'in')!==false||$tx['type']==='depot'; ?>
  <tr>
    <td style="font-family:monospace;font-size:.75rem"><?php echo htmlspecialchars($tx['reference'],ENT_QUOTES,'UTF-8');?></td>
    <td style="font-size:.82rem"><b><?php echo htmlspecialchars(($tx['first_name']??'').' '.($tx['last_name']??''),ENT_QUOTES,'UTF-8');?></b></td>
    <td><span class="badge badge-<?php echo $in?'green':'blue';?>"><?php echo str_replace('_',' ',$tx['type']);?></span></td>
    <td style="font-family:monospace;font-size:.72rem"><?php echo htmlspecialchars(substr($tx['numero'],-8),ENT_QUOTES,'UTF-8');?></td>
    <td style="text-align:right;font-weight:700;color:<?php echo $in?'var(--green,#16a34a)':'var(--red,#dc2626)';?>"><?php echo ($in?'+':'-').format_money($tx['montant']);?></td>
    <td><span class="badge badge-<?php echo $tx['statut']==='terminee'?'green':'blue';?>"><?php echo ucfirst($tx['statut']);?></span></td>
    <td style="font-size:.75rem;color:var(--gray400)"><?php echo format_datetime($tx['cree_le']);?></td>
  </tr>
  <?php endforeach; ?>
  <?php if(empty($txs)): ?><tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--gray400)">Aucune transaction</td></tr><?php endif; ?>
  </tbody></table>
</div>
<?php if($pg['total_pages']>1): ?>
<div style="display:flex;justify-content:center;gap:.5rem;margin-top:1rem">
  <?php if($pg['has_prev']): ?><a href="?page=<?php echo $pg['current']-1;?>&type=<?php echo $type_f;?>" class="admin-btn">← Préc.</a><?php endif; ?>
  <span style="font-size:.8rem;color:var(--gray400);padding:.5rem">Page <?php echo $pg['current'];?>/<?php echo $pg['total_pages'];?></span>
  <?php if($pg['has_next']): ?><a href="?page=<?php echo $pg['current']+1;?>&type=<?php echo $type_f;?>" class="admin-btn">Suiv. →</a><?php endif; ?>
</div>
<?php endif; ?>
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
})();
</script></body></html>
