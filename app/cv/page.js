'use client';
import { useEffect } from 'react';
import Link from 'next/link';

export default function CV() {
  useEffect(() => {
    const navbar = document.getElementById('cv-navbar');
    const handleScroll = () => navbar?.classList.toggle('scrolled', window.scrollY > 40);
    window.addEventListener('scroll', handleScroll, { passive: true });

    const obs = new IntersectionObserver((entries) => {
      entries.forEach(e => { if (e.isIntersecting) { setTimeout(() => e.target.classList.add('visible'), 60); obs.unobserve(e.target); } });
    }, { threshold: 0.08 });
    document.querySelectorAll('.reveal').forEach(el => obs.observe(el));

    // Track analytics
    fetch('/api/analytics', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({event_type:'page_view',page:'/cv'}) }).catch(()=>{});
    return () => { window.removeEventListener('scroll', handleScroll); obs.disconnect(); };
  }, []);

  return (
    <>
      <style>{`
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{--ink:#070708;--layer:#0d0d10;--card:#111116;--blue:#007BFF;--cyan:#00C2FF;--white:#f0f0f4;--muted:rgba(240,240,244,.42);--border:rgba(240,240,244,.07);--ease:cubic-bezier(.22,1,.36,1)}
        html{scroll-behavior:smooth}
        body{background:var(--ink);color:var(--white);font-family:'Space Grotesk',sans-serif;font-weight:300;line-height:1.65;overflow-x:hidden}
        #cv-navbar{position:fixed;top:0;left:0;right:0;z-index:100;display:flex;align-items:center;justify-content:space-between;padding:1.3rem 5%;transition:background .5s,padding .5s,border-color .5s;border-bottom:1px solid transparent}
        #cv-navbar.scrolled{background:rgba(7,7,8,.93);backdrop-filter:blur(18px);border-color:var(--border);padding:.85rem 5%}
        .cv-logo{font-family:'Syne',sans-serif;font-size:1.4rem;font-weight:800;letter-spacing:.06em;color:var(--white);text-decoration:none}
        .cv-logo em{color:var(--blue);font-style:normal}
        .cv-nav-links{display:flex;gap:2rem;list-style:none}
        .cv-nav-links a{font-size:.72rem;letter-spacing:.14em;text-transform:uppercase;color:var(--muted);text-decoration:none;transition:color .2s;font-family:'Space Mono',monospace}
        .cv-nav-links a:hover,.cv-nav-links a.active{color:var(--white)}
        .btn-blue{background:var(--blue);color:#fff;border:none;padding:.55rem 1.25rem;font-family:'Space Grotesk',sans-serif;font-size:.75rem;font-weight:500;letter-spacing:.04em;cursor:pointer;text-decoration:none;display:inline-block;transition:background .2s,transform .2s}
        .btn-blue:hover{background:#339DFF;transform:translateY(-2px)}
        .btn-outline{background:transparent;color:var(--white);border:1px solid rgba(240,240,244,.22);padding:.55rem 1.25rem;font-family:'Space Grotesk',sans-serif;font-size:.75rem;letter-spacing:.04em;cursor:pointer;text-decoration:none;display:inline-block;transition:border-color .2s}
        .btn-outline:hover{border-color:var(--white)}
        .cv-hero{min-height:42vh;display:flex;flex-direction:column;justify-content:flex-end;padding:8rem 5% 3.5rem;border-bottom:1px solid var(--border);position:relative;overflow:hidden}
        .cv-hero::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 75% -5%,rgba(0,123,255,.09),transparent 50%),radial-gradient(ellipse at 20% 110%,rgba(0,194,255,.05),transparent 50%);pointer-events:none}
        .cv-eyebrow{font-family:'Space Mono',monospace;font-size:.7rem;color:var(--blue);letter-spacing:.18em;margin-bottom:1rem;position:relative;z-index:1}
        .cv-hero h1{font-family:'Syne',sans-serif;font-size:clamp(2.8rem,6.5vw,6rem);font-weight:800;line-height:.9;letter-spacing:-.01em;position:relative;z-index:1}
        .cv-hero h1 em{font-style:normal;color:var(--blue)}
        .cv-hero h1 span{color:var(--cyan)}
        .cv-tagline{margin-top:1.2rem;font-size:.9rem;color:var(--muted);letter-spacing:.05em;position:relative;z-index:1;font-family:'Space Mono',monospace}
        .cv-actions{margin-top:2rem;display:flex;gap:.75rem;flex-wrap:wrap;position:relative;z-index:1}
        .cv-body{display:grid;grid-template-columns:270px 1fr;min-height:70vh}
        .cv-side{background:var(--layer);border-right:1px solid var(--border);padding:2.5rem 1.8rem}
        .cv-main{padding:2.8rem 3rem}
        .photo-wrap{display:flex;justify-content:center;margin-bottom:2rem}
        .photo-ring{width:110px;height:110px;border-radius:50%;border:2px solid var(--blue);background:var(--card);display:flex;align-items:center;justify-content:center;overflow:hidden;position:relative}
        .photo-ring img{width:100%;height:100%;object-fit:cover;object-position:center top;border-radius:50%}
        .s-label{font-family:'Space Mono',monospace;font-size:.58rem;letter-spacing:.2em;text-transform:uppercase;color:var(--blue);margin-bottom:.8rem;display:flex;align-items:center;gap:.5rem}
        .s-label::after{content:'';flex:1;height:1px;background:rgba(0,123,255,.2)}
        .s-block{margin-bottom:1.7rem}
        .c-item{margin-bottom:.65rem}
        .c-item small{display:block;font-family:'Space Mono',monospace;font-size:.55rem;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);margin-bottom:.15rem}
        .c-item a,.c-item span{font-size:.78rem;color:var(--white);text-decoration:none}
        .c-item a:hover{color:var(--blue)}
        .sk{margin-bottom:.7rem}
        .sk-top{display:flex;justify-content:space-between;margin-bottom:.28rem}
        .sk-top span{font-size:.75rem;color:var(--white);font-weight:400}
        .sk-top small{font-family:'Space Mono',monospace;font-size:.6rem;color:var(--muted)}
        .sk-track{height:2px;background:rgba(240,240,244,.06);border-radius:1px}
        .sk-fill{height:2px;border-radius:1px;background:linear-gradient(90deg,var(--blue),var(--cyan))}
        .pills{display:flex;flex-wrap:wrap;gap:.3rem}
        .pill{border:1px solid var(--border);padding:.2rem .6rem;font-size:.65rem;letter-spacing:.05em;color:var(--muted);font-family:'Space Mono',monospace}
        .pill.hi{border-color:rgba(0,123,255,.4);color:var(--blue)}
        .pill.cyan{border-color:rgba(0,194,255,.4);color:var(--cyan)}
        .lang-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:.55rem}
        .lang-row span{font-size:.76rem;color:var(--white)}
        .ldots{display:flex;gap:4px}
        .ld{width:6px;height:6px;border-radius:50%;border:1px solid var(--blue)}
        .ld.on{background:linear-gradient(135deg,var(--blue),var(--cyan))}
        .m-section{margin-bottom:2.2rem}
        .m-title{font-family:'Space Mono',monospace;font-size:.62rem;letter-spacing:.2em;text-transform:uppercase;color:var(--blue);margin-bottom:1.3rem;display:flex;align-items:center;gap:.75rem}
        .m-title::after{content:'';flex:1;height:1px;background:var(--border)}
        .badge{display:inline-flex;align-items:center;gap:.4rem;border:1px solid rgba(0,123,255,.3);padding:.25rem .75rem;font-family:'Space Mono',monospace;font-size:.62rem;letter-spacing:.08em;color:var(--blue);margin-bottom:1rem}
        .profil{font-size:.86rem;color:var(--muted);line-height:1.9;max-width:540px}
        .profil strong{color:var(--white);font-weight:500}
        .strengths{display:grid;grid-template-columns:1fr 1fr;gap:1px;background:var(--border);margin-top:1.4rem}
        .strength{background:var(--card);padding:.85rem 1rem;transition:background .3s;position:relative;overflow:hidden}
        .strength:hover{background:#161620}
        .s-icon{font-family:'Space Mono',monospace;font-size:.58rem;letter-spacing:.12em;text-transform:uppercase;color:var(--blue);margin-bottom:.3rem}
        .s-text{font-size:.77rem;color:var(--muted);line-height:1.6}
        .exp{margin-bottom:1.6rem;padding-bottom:1.6rem;border-bottom:1px solid var(--border)}
        .exp:last-child{border-bottom:none;margin-bottom:0;padding-bottom:0}
        .exp-top{display:flex;justify-content:space-between;align-items:flex-start;gap:.5rem;flex-wrap:wrap}
        .exp-title{font-size:.88rem;font-weight:600;color:var(--white)}
        .exp-period{font-family:'Space Mono',monospace;font-size:.65rem;color:var(--blue);white-space:nowrap;flex-shrink:0}
        .exp-co{font-size:.72rem;color:var(--muted);margin:.3rem 0 .65rem;font-style:italic}
        .exp-ul{list-style:none;padding:0;margin:0}
        .exp-ul li{font-size:.8rem;color:var(--muted);line-height:1.75;padding-left:1rem;position:relative;margin-bottom:.2rem}
        .exp-ul li::before{content:'';position:absolute;left:0;top:.58em;width:4px;height:4px;border-radius:50%;background:linear-gradient(135deg,var(--blue),var(--cyan))}
        .stack-tags{display:flex;flex-wrap:wrap;gap:.3rem;margin-top:.5rem}
        .stack-tag{background:rgba(0,123,255,.1);border:1px solid rgba(0,123,255,.25);padding:.1rem .5rem;font-family:'Space Mono',monospace;font-size:.6rem;color:var(--blue);letter-spacing:.04em}
        .f-row{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;padding:.7rem 0;border-bottom:1px solid var(--border)}
        .f-row:last-child{border-bottom:none}
        .f-title{font-size:.82rem;font-weight:500;color:var(--white)}
        .f-org{font-size:.7rem;color:var(--muted);margin-top:.2rem}
        .f-year{font-family:'Space Mono',monospace;font-size:.65rem;color:var(--blue);white-space:nowrap;flex-shrink:0}
        .tarif-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:1px;background:var(--border);margin-bottom:.75rem}
        .tarif-card{background:var(--card);padding:1rem .9rem;position:relative;overflow:hidden;transition:background .3s}
        .tarif-card:hover{background:#161620}
        .tarif-card.full{grid-column:1/-1;background:#0e0e14}
        .t-name{font-size:.8rem;font-weight:500;color:var(--white);margin-bottom:.2rem}
        .t-desc{font-size:.68rem;color:var(--muted);line-height:1.55;margin-bottom:.5rem}
        .t-price{font-family:'Syne',sans-serif;font-size:1.5rem;font-weight:800;background:linear-gradient(90deg,var(--blue),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
        .t-from{font-family:'Space Mono',monospace;font-size:.6rem;color:var(--muted);-webkit-text-fill-color:var(--muted);margin-right:.2rem}
        .t-per{font-family:'Space Mono',monospace;font-size:.6rem;color:var(--muted);-webkit-text-fill-color:var(--muted);margin-left:.25rem}
        .tarif-note{font-size:.74rem;color:var(--muted)}
        .tarif-note a{color:var(--blue);text-decoration:none}
        .channel-btn{display:inline-flex;align-items:center;gap:.45rem;border:1px solid var(--border);padding:.5rem 1rem;font-size:.72rem;letter-spacing:.06em;text-transform:uppercase;color:var(--muted);text-decoration:none;font-family:'Space Mono',monospace;transition:border-color .2s,color .2s,background .2s}
        .channel-btn:hover{border-color:var(--blue);color:var(--white);background:rgba(0,123,255,.07)}
        .channel-btn svg{width:13px;height:13px;fill:currentColor;flex-shrink:0}
        .cv-footer{padding:2.5rem 5%;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem}
        .reveal{opacity:0;transform:translateY(24px);transition:opacity .7s var(--ease),transform .7s var(--ease)}
        .reveal.visible{opacity:1;transform:none}
        @media(max-width:860px){.cv-body{grid-template-columns:1fr}.cv-side{border-right:none;border-bottom:1px solid var(--border)}.cv-main{padding:2rem 1.5rem}.tarif-grid{grid-template-columns:1fr}.strengths{grid-template-columns:1fr}.cv-nav-links{display:none}}
      `}</style>

      <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600&family=Syne:wght@700;800&family=Space+Mono&display=swap" rel="stylesheet" />

      <nav id="cv-navbar">
        <Link href="/" className="cv-logo">EX<em>.</em>AUCÉ</Link>
        <ul className="cv-nav-links">
          <li><Link href="/#about">À Propos</Link></li>
          <li><Link href="/#portfolio">Travaux</Link></li>
          <li><Link href="/#services">Services</Link></li>
          <li><Link href="/cv" className="active">CV & Tarifs</Link></li>
          <li><Link href="/#contact">Contact</Link></li>
        </ul>
      </nav>

      <header className="cv-hero">
        <p className="cv-eyebrow">// curriculum_vitae · 2025</p>
        <h1>DÉVELOPPEUR<br/><em>WEB</em> & <span>CRÉATIF</span></h1>
        <p className="cv-tagline">HTML · CSS · JS · PHP · Montage · Motion · Design</p>
        <div className="cv-actions">
          <a href="/CV_Exauce_Attinganme.pdf" download className="btn-blue"
             onClick={() => fetch('/api/analytics',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({event_type:'cv_download',page:'/cv'})}).catch(()=>{})}>
            ↓ Télécharger le PDF
          </a>
          <a href="https://wa.me/22901495140" target="_blank" rel="noopener noreferrer" className="btn-outline">💬 WhatsApp</a>
        </div>
      </header>

      <div className="cv-body">
        <aside className="cv-side">
          <div className="photo-wrap">
            <div className="photo-ring">
              <img src="/images/Exaucé.png" alt="Exaucé Attinganmè" loading="lazy" />
            </div>
          </div>
          <div className="s-block">
            <div className="s-label">Contact</div>
            <div className="c-item"><small>Email</small><a href="mailto:Exaucejoel29@gmail.com">Exaucejoel29@gmail.com</a></div>
            <div className="c-item"><small>WhatsApp · Appel</small><a href="tel:+22901495140">+229 01 49 51 40 64</a></div>
            <div className="c-item"><small>Localisation</small><span>Abomey-Calavi, Bénin</span></div>
          </div>
          <div className="s-block">
            <div className="s-label">Développement Web</div>
            {[['HTML · CSS','92%'],['JavaScript','85%'],['PHP','75%'],['React','70%'],['WordPress','80%']].map(([name,pct]) => (
              <div key={name} className="sk">
                <div className="sk-top"><span>{name}</span><small>{pct}</small></div>
                <div className="sk-track"><div className="sk-fill" style={{width:pct}}></div></div>
              </div>
            ))}
          </div>
          <div className="s-block">
            <div className="s-label">Montage · Design</div>
            <div className="pills">
              {['Canva','CapCut','InShot'].map(t => <span key={t} className="pill cyan">{t}</span>)}
              {['Figma','Photoshop','Premiere Pro','After Effects'].map(t => <span key={t} className="pill">{t}</span>)}
            </div>
          </div>
          <div className="s-block">
            <div className="s-label">Autres outils</div>
            <div className="pills">
              {['Git · GitHub','MySQL','API REST'].map(t => <span key={t} className="pill hi">{t}</span>)}
              {['Node.js','Socket.io','VS Code'].map(t => <span key={t} className="pill">{t}</span>)}
            </div>
          </div>
          <div className="s-block">
            <div className="s-label">Langues</div>
            {[['Français',5],['Anglais',3],['Fon',5]].map(([lang,lvl]) => (
              <div key={lang} className="lang-row">
                <span>{lang}</span>
                <div className="ldots">{[1,2,3,4,5].map(i => <div key={i} className={`ld${i<=lvl?' on':''}`}></div>)}</div>
              </div>
            ))}
          </div>
          <div className="s-block">
            <div className="s-label">Intérêts</div>
            <div className="pills">
              <span className="pill cyan">Motion Design</span>
              <span className="pill hi">IA générative</span>
              <span className="pill">Musique</span>
              <span className="pill">Danse</span>
              <span className="pill">Art visuel</span>
            </div>
          </div>
        </aside>

        <main className="cv-main">
          <div className="m-section">
            <div className="m-title">Profil</div>
            <div className="badge">// autodidacte · formation continue</div>
            <p className="profil">
              Développeur web <strong>front-end</strong> avec une forte sensibilité visuelle et créative.
              Je construis des <strong>interfaces claires et interactives</strong> — plateformes avec messagerie temps réel,
              notifications email, designs animés — en JavaScript, PHP et React.
              En parallèle, je réalise des <strong>montages vidéo et des designs animés</strong> percutants avec Canva, CapCut,
              InShot, Premiere Pro et After Effects. Autodidacte dans l'âme, je me forme en permanence.
            </p>
            <div className="strengths">
              {[
                ['Créativité','Je conçois avant de coder. Chaque interface est pensée pour l\'œil autant que pour la logique.'],
                ['Polyvalence','Dev front-end + montage vidéo + design : je couvre la chaîne créative de A à Z.'],
                ['Rigueur','Code propre, délais tenus, communication claire. Je traite chaque projet avec sérieux.'],
                ['Curiosité','J\'apprends chaque jour — nouvelles bibliothèques, nouveaux outils, nouvelles techniques.'],
              ].map(([icon,text]) => (
                <div key={icon} className="strength reveal">
                  <div className="s-icon">// {icon}</div>
                  <div className="s-text">{text}</div>
                </div>
              ))}
            </div>
          </div>

          <div className="m-section">
            <div className="m-title">Expériences</div>
            {[
              { title:'Développeur Web Front-End', period:'2023 – Présent', co:'Freelance · Projets personnels',
                items:['Création d\'interfaces web modernes et responsives (HTML, CSS, JavaScript, PHP)','Développement de plateformes avec messagerie en temps réel et notifications email','Composants React, intégration d\'APIs REST, optimisation des performances','Design et intégration de maquettes Figma/Canva en code propre et accessible'],
                tags:['HTML','CSS','JavaScript','PHP','React','Node.js'] },
              { title:'Monteur Vidéo · Motion Design', period:'2022 – Présent', co:'Projets freelance · Chaîne YouTube',
                items:['Montage vidéo professionnel (Premiere Pro, After Effects, CapCut, InShot)','Création de designs animés et effets motion design percutants','Résumés podcast, contenus YouTube, thumbnails et visuels graphiques','Formats optimisés pour YouTube, TikTok, Instagram et Facebook'],
                tags:['Canva','CapCut','InShot','Premiere Pro','After Effects'] },
              { title:'Développeur · Créateur', period:'Juin 2025 – Présent', co:'STRATEEK — Collectif créatif (bénévole)',
                items:['Développement front-end sur des projets web collectifs','Gestion des décisions techniques et artistiques au sein de l\'équipe','Création de contenus visuels (graphisme, montage, design)'],
                tags:[] },
            ].map(exp => (
              <div key={exp.title} className="exp reveal">
                <div className="exp-top"><span className="exp-title">{exp.title}</span><span className="exp-period">{exp.period}</span></div>
                <div className="exp-co">{exp.co}</div>
                <ul className="exp-ul">{exp.items.map(li => <li key={li}>{li}</li>)}</ul>
                {exp.tags.length > 0 && <div className="stack-tags">{exp.tags.map(t => <span key={t} className="stack-tag">{t}</span>)}</div>}
              </div>
            ))}
          </div>

          <div className="m-section">
            <div className="m-title">Formation</div>
            {[
              ['Développement Web — Autodidacte','MDN · freeCodeCamp · Projets réels · Documentation officielle','2021 – Présent'],
              ['Montage Vidéo · Motion Design — Autodidacte','Tutoriels · Expérimentation · After Effects · Premiere Pro','2022 – Présent'],
              ['Certification Professionnelle Numérique','Bénin Horizon (ex-PPIEAJ)','2022 – 2024'],
            ].map(([title,org,year]) => (
              <div key={title} className="f-row reveal">
                <div><div className="f-title">{title}</div><div className="f-org">{org}</div></div>
                <div className="f-year">{year}</div>
              </div>
            ))}
          </div>

          <div className="m-section">
            <div className="m-title">Prestations & Tarifs</div>
            <div className="tarif-grid">
              {[
                ['Visuel · Thumbnail (Canva)','Design optimisé clics · Livraison 48h · Retouches incluses','15 €'],
                ['Montage vidéo court','CapCut · InShot · Sous-titres · Multi-formats · 3 jours','40 €'],
                ['Montage vidéo long','Motion design · Habillage graphique · Optimisation plateforme','80 €'],
                ['Landing page','JS · PHP · Design · Formulaire · SEO de base · 5 jours','120 €'],
                ['Site vitrine','React ou WordPress · Responsive · SEO · 10 jours','200 €'],
                ['Maintenance mensuelle','Mises à jour · Corrections · Sauvegardes · Support WhatsApp','30 €/mois'],
              ].map(([name,desc,price]) => (
                <div key={name} className="tarif-card reveal">
                  <div className="t-name">{name}</div>
                  <div className="t-desc">{desc}</div>
                  <div className="t-price"><span className="t-from">à partir de </span>{price}</div>
                </div>
              ))}
              <div className="tarif-card full reveal">
                <div className="t-name">🔥 Pack Créateur — Vidéo + Visuels</div>
                <div className="t-desc">2 montages vidéo · 4 visuels Canva · Thumbnails · Suivi WhatsApp mensuel inclus</div>
                <div className="t-price"><span className="t-from">à partir de </span>80 €<span className="t-per">/mois</span></div>
              </div>
            </div>
            <p className="tarif-note reveal">Devis gratuit · Tarif étudiant / association disponible &nbsp;
              <a href="https://wa.me/22901495140?text=Bonjour%20Exauc%C3%A9%2C%20je%20voudrais%20un%20devis" target="_blank" rel="noopener noreferrer">Demander un devis →</a>
            </p>
          </div>

          <div className="m-section">
            <div className="m-title">Me contacter</div>
            <div style={{display:'flex',gap:'.6rem',flexWrap:'wrap'}}>
              <a href="https://wa.me/22901495140" target="_blank" rel="noopener noreferrer" className="channel-btn">
                <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                WhatsApp
              </a>
              <a href="mailto:Exaucejoel29@gmail.com" className="channel-btn">
                <svg viewBox="0 0 24 24"><path d="M20 4H4a2 2 0 00-2 2v12a2 2 0 002 2h16a2 2 0 002-2V6a2 2 0 00-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                Email
              </a>
              <a href="tel:+22901495140" className="channel-btn">
                <svg viewBox="0 0 24 24"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
                Appel direct
              </a>
            </div>
          </div>
        </main>
      </div>

      <footer className="cv-footer">
        <Link href="/" style={{fontFamily:'Syne,sans-serif',fontSize:'1.1rem',fontWeight:800,color:'rgba(240,240,244,.4)',textDecoration:'none'}}>EX<em style={{color:'#007BFF',fontStyle:'normal'}}>.</em>AUCÉ</Link>
        <Link href="/" style={{fontSize:'.7rem',color:'rgba(240,240,244,.4)',textDecoration:'none',fontFamily:'Space Mono,monospace'}}>← Retour au portfolio</Link>
        <div style={{fontFamily:'Space Mono,monospace',fontSize:'.65rem',color:'rgba(240,240,244,.15)'}}>© 2025 EXAUCÉ · All rights reserved</div>
      </footer>
    </>
  );
}
