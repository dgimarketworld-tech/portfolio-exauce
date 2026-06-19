'use client';
import { useState, useEffect } from 'react';
import Link from 'next/link';

const DEFAULT_PROJECTS = [
  { id: 1, title: 'TechVibes Africa', type: 'Thumbnail YouTube', stats: 'Thumbnail optimisée · CTR amélioré', image_url: '/images/Projet 1.png', mockup_label: 'TECHVIBES', bg_class: 'port-bg-1', display_order: 1 },
  { id: 2, title: 'FitNation Africa', type: 'Montage Vidéo', stats: 'Montage court · Rétention améliorée', image_url: '/images/projet 2.png', mockup_label: 'FIT', bg_class: 'port-bg-2', display_order: 2 },
  { id: 3, title: 'BusinessAfrica.tv', type: 'Site Web', stats: 'Livré en 7 jours · Responsive · SEO de base', image_url: '/images/Projet 3.png', mockup_label: 'WEB', bg_class: 'port-bg-3', display_order: 3 },
  { id: 4, title: 'King Kora Music', type: 'Thumbnail + Montage', stats: 'Thumbnail + montage · Résultats visibles', image_url: '/images/Projet 4.png', mockup_label: 'KING', bg_class: 'port-bg-4', display_order: 4 },
  { id: 5, title: 'ModeBénin Shop', type: 'Site Web E-commerce', stats: 'Site e-commerce livré · Design mobile-first', image_url: null, mockup_label: 'MODE', bg_class: 'port-bg-5', display_order: 5 },
  { id: 6, title: 'Lifestyle Béninoise', type: 'Montage Vlog', stats: 'Montage vlog · Cuts dynamiques · Multi-formats', image_url: null, mockup_label: 'VLOG', bg_class: 'port-bg-6', display_order: 6 },
];

export default function Home() {
  const [projects, setProjects] = useState(DEFAULT_PROJECTS);
  const [testimonials, setTestimonials] = useState([]);
  const [formData, setFormData] = useState({ name: '', email: '', message: '' });
  const [formStatus, setFormStatus] = useState('idle');

  useEffect(() => {
    fetch('/api/projects').then(r => r.json()).then(d => { if (d?.length) setProjects(d); }).catch(() => {});
    fetch('/api/testimonials').then(r => r.json()).then(d => { if (d) setTestimonials(d); }).catch(() => {});
    fetch('/api/analytics', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ event_type: 'page_view', page: '/' }) }).catch(() => {});
  }, []);

  useEffect(() => {
    const navbar = document.getElementById('navbar');
    const handleScroll = () => navbar?.classList.toggle('scrolled', window.scrollY > 40);
    window.addEventListener('scroll', handleScroll, { passive: true });
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  useEffect(() => {
    const obs = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting) { setTimeout(() => e.target.classList.add('visible'), 80); obs.unobserve(e.target); }
      });
    }, { threshold: 0.08 });
    document.querySelectorAll('.reveal').forEach(el => obs.observe(el));
    return () => obs.disconnect();
  }, [projects, testimonials]);

  async function handleSubmit(e) {
    e.preventDefault();
    setFormStatus('sending');
    try {
      const res = await fetch('/api/contact', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(formData) });
      if (res.ok) {
        setFormStatus('sent');
        setFormData({ name: '', email: '', message: '' });
        fetch('/api/analytics', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ event_type: 'contact_submit', page: '/' }) }).catch(() => {});
      } else { setFormStatus('error'); }
    } catch { setFormStatus('error'); }
  }

  function playVideo(e) {
    const overlay = e.currentTarget.querySelector('.video-overlay');
    if (overlay) overlay.innerHTML = '<p style="font-size:.8rem;letter-spacing:.1em;text-transform:uppercase;color:rgba(244,243,239,.4)">Ajoute ton lien YouTube dans l\'admin 🎬</p>';
  }

  return (
    <>
      <nav id="navbar">
        <Link href="/" className="logo">EX<span>.</span>AUCÉ</Link>
        <ul className="nav-links">
          <li><a href="#about">À Propos</a></li>
          <li><a href="#services">Services</a></li>
          <li><a href="#portfolio">Mon Travail</a></li>
          <li><Link href="/cv">CV & Tarifs</Link></li>
          <li><a href="#contact">Contact</a></li>
        </ul>
        <a href="#contact" className="btn-blue">Démarrer un projet</a>
      </nav>

      <section className="hero">
        <p className="hero-eyebrow">Développeur Web · Monteur Vidéo · Designer</p>
        <h1>Construis ta<br/><em>présence.</em><br/>Croîs <em>vite.</em></h1>
        <p className="hero-sub">Je conçois des interfaces web modernes et des montages vidéo percutants — du code propre au contenu qui engage.</p>
        <div className="hero-ctas">
          <a href="#contact" className="btn-blue">Démarrer un projet</a>
          <a href="#portfolio" className="btn-outline">Voir mon travail</a>
        </div>
        <div className="hero-line"><span></span></div>
      </section>

      <section className="stats">
        <div className="stats-grid">
          <div className="stat-card reveal"><div className="stat-number">20+</div><div className="stat-label">projets livrés</div></div>
          <div className="stat-card reveal"><div className="stat-number">3 ans</div><div className="stat-label">d'expérience web & vidéo</div></div>
          <div className="stat-card reveal"><div className="stat-number">100%</div><div className="stat-label">délais respectés</div></div>
          <div className="stat-card reveal"><div className="stat-number">5★</div><div className="stat-label">satisfaction client</div></div>
        </div>
      </section>

      <section className="about" id="about">
        <div className="about-photo reveal">
          <img src="/images/Exaucé.png" alt="Exaucé" />
          <div className="about-photo-badge">EXAUCÉ<br/><span style={{fontSize:'.7rem',letterSpacing:'.06em',fontWeight:300,opacity:.7}}>Dev Web & Monteur</span></div>
        </div>
        <div className="about-text reveal">
          <p className="section-label">À Propos</p>
          <h2 className="about-title">Code propre.<br/>Contenu qui <em style={{fontStyle:'normal',color:'var(--blue)'}}>engage.</em></h2>
          <p className="about-desc">Développeur web front-end avec une forte sensibilité créative, je construis des interfaces modernes et interactives en HTML, CSS, JavaScript, PHP et React. En parallèle, je réalise des montages vidéo et des designs visuels percutants avec Canva, CapCut, Premiere Pro et After Effects.</p>
          <p className="about-desc">Autodidacte depuis 2021, je couvre la chaîne créative de A à Z — du code à la vidéo. Chaque projet est traité avec rigueur, sans compromis sur la qualité ni sur les délais.</p>
          <div className="about-tags">
            <span className="tag">HTML · CSS · JS</span>
            <span className="tag">PHP · React</span>
            <span className="tag">Montage Vidéo</span>
            <span className="tag">Thumbnails</span>
            <span className="tag">WordPress</span>
            <span className="tag">Canva · CapCut</span>
          </div>
        </div>
      </section>

      <section className="video-section" id="video">
        <div className="video-header reveal">
          <p className="section-label" style={{marginBottom:0}}>Showreel</p>
          <div className="video-title">Regarde ce que<br/>je peux faire.</div>
        </div>
        <div className="video-player reveal" onClick={playVideo}>
          <div className="port-bg-1" style={{position:'absolute',inset:0,opacity:.9}}></div>
          <div style={{position:'absolute',top:'12%',left:'6%',fontFamily:'Bebas Neue,sans-serif',fontSize:'clamp(2rem,6vw,5rem)',letterSpacing:'.04em',opacity:.08}}>EXAUCÉ STUDIOS</div>
          <div className="video-overlay">
            <div className="play-btn">
              <svg width="24" height="24" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
            </div>
            <span className="video-label">Showreel 2025 — Cliquer pour regarder</span>
          </div>
        </div>
        <div className="video-meta reveal">
          <div className="video-meta-item"><strong>Durée</strong>1 min 48 sec</div>
          <div className="video-meta-item"><strong>Projets inclus</strong>8 réalisations</div>
          <div className="video-meta-item"><strong>Mis à jour</strong>Juin 2025</div>
          <div className="video-meta-item"><strong>Outils</strong>CapCut · Premiere Pro</div>
        </div>
      </section>

      <section className="portfolio" id="portfolio">
        <div className="portfolio-header reveal" style={{display:'flex',justifyContent:'space-between',alignItems:'flex-end',marginBottom:'4rem',flexWrap:'wrap',gap:'2rem'}}>
          <p className="section-label" style={{marginBottom:0}}>Portfolio</p>
          <div style={{fontFamily:'Bebas Neue,sans-serif',fontSize:'clamp(2rem,4vw,3.2rem)',letterSpacing:'.04em',lineHeight:1}}>Travaux récents.</div>
        </div>
        <div className="portfolio-grid">
          {projects.map((p) => (
            <div key={p.id} className="portfolio-item reveal">
              {p.image_url
                ? <img src={p.image_url} alt={p.title} style={{position:'absolute',inset:0,width:'100%',height:'100%',objectFit:'cover',zIndex:0}}/>
                : <div className={p.bg_class} style={{position:'absolute',inset:0}}></div>
              }
              {p.image_url && <div style={{position:'absolute',inset:0,background:'linear-gradient(to bottom,rgba(0,0,0,0) 20%,rgba(0,0,0,.85) 100%)',zIndex:1}}></div>}
              <div className="port-mockup" style={{zIndex:2}}>{p.mockup_label}</div>
              <div className="portfolio-info" style={{zIndex:3}}>
                <div className="portfolio-type">{p.type}</div>
                <div className="portfolio-name">{p.title}</div>
                <div className="portfolio-stat">{p.stats}</div>
              </div>
            </div>
          ))}
        </div>
      </section>

      <section className="process" id="process">
        <p className="section-label reveal">Comment ça marche</p>
        <div className="process-grid">
          {[
            {n:'01',t:'Accord & Paiement',d:"On valide ton idée et tu règles la commande pour bloquer ta place."},
            {n:'02',t:'Envoi des fichiers',d:"Tu m'envoies tes rushs — vidéos, photos, logos — par lien ou WhatsApp."},
            {n:'03',t:'Création',d:"Je m'occupe de tout : montage, design, code et optimisation."},
            {n:'04',t:'Livraison',d:"Tu reçois ton contenu final, prêt à publier ou déployer."},
          ].map(s => (
            <div key={s.n} className="process-step reveal">
              <div className="step-num">{s.n}</div>
              <div className="step-dot"></div>
              <div className="step-title">{s.t}</div>
              <p className="step-text">{s.d}</p>
            </div>
          ))}
        </div>
      </section>

      <section className="services" id="services">
        <div className="services-header">
          <p className="section-label reveal" style={{marginBottom:0}}>Mes services</p>
          <div className="services-title reveal">Ce que je fais<br/>pour toi.</div>
        </div>
        <div className="services-grid">
          {[
            {n:'01 — Vidéo',name:'Montage Vidéo',desc:'Cuts précis, sous-titres, musique, effets motion — avec CapCut, InShot ou Premiere Pro. Formats optimisés YouTube, TikTok, Instagram et Facebook.'},
            {n:'02 — Design',name:'Visuels & Thumbnails',desc:'Miniatures percutantes, affiches, graphismes animés conçus sur Canva. Livraison rapide avec retouches incluses.'},
            {n:'03 — Web',name:'Sites Web',desc:"Landing pages, sites vitrines et plateformes en HTML/CSS/JS, PHP ou WordPress. Responsive, propre et livré dans les délais."},
            {n:'04 — Pack',name:'Pack Créateur',desc:'Vidéo + visuels + suivi mensuel. Une offre complète pour les créateurs de contenu qui veulent avancer sans se disperser.'},
          ].map(s => (
            <div key={s.n} className="service-card reveal">
              <div className="service-icon">{s.n}</div>
              <div className="service-name">{s.name}</div>
              <p className="service-desc">{s.desc}</p>
            </div>
          ))}
        </div>
      </section>

      {testimonials.length > 0 && (
        <section className="testimonials-section" id="testimonials">
          <div className="services-header">
            <p className="section-label reveal" style={{marginBottom:0}}>Témoignages</p>
            <div className="services-title reveal">Ce qu'ils<br/>disent.</div>
          </div>
          <div className="testimonials-grid">
            {testimonials.map(t => (
              <div key={t.id} className="testimonial-card reveal">
                <div className="t-stars">{'★'.repeat(Number(t.rating))}</div>
                <p className="t-message">"{t.message}"</p>
                <div className="t-author">
                  <div className="t-name">{t.name}</div>
                  <div className="t-role">{t.role}</div>
                </div>
              </div>
            ))}
          </div>
        </section>
      )}

      <section className="contact" id="contact">
        <div className="contact-inner">
          <p className="section-label reveal">Travaillons ensemble</p>
          <div className="contact-title reveal">Prêt à passer<br/>au niveau supérieur ?</div>
          <p className="contact-sub reveal">Une idée suffit pour commencer.</p>
          <form onSubmit={handleSubmit}>
            <div className="form-row reveal">
              <input type="text" placeholder="Ton nom" required value={formData.name} onChange={e => setFormData({...formData, name: e.target.value})} />
            </div>
            <div className="form-row reveal">
              <input type="email" placeholder="Ton email" required value={formData.email} onChange={e => setFormData({...formData, email: e.target.value})} />
            </div>
            <div className="form-row reveal">
              <textarea placeholder="Parle-moi de ton projet..." value={formData.message} onChange={e => setFormData({...formData, message: e.target.value})} />
            </div>
            <div className="form-row reveal" style={{marginTop:'2.5rem'}}>
              <button type="submit" className="btn-blue" disabled={formStatus === 'sending'} style={formStatus === 'sent' ? {background:'#0d7a45'} : {}}>
                {formStatus === 'idle' && 'Envoyer le message'}
                {formStatus === 'sending' && 'Envoi...'}
                {formStatus === 'sent' && 'Message envoyé ✓'}
                {formStatus === 'error' && 'Erreur — réessaie'}
              </button>
              {formStatus === 'sent' && <p style={{marginTop:'1rem',fontSize:'.85rem',color:'var(--blue)'}}>Je reviens vers toi dans les 24h.</p>}
            </div>
          </form>
          <div className="reveal" style={{marginTop:'2.5rem',display:'flex',gap:'1rem',flexWrap:'wrap'}}>
            <a href="https://wa.me/22901495140" target="_blank" rel="noopener noreferrer" className="btn-blue"
               onClick={() => fetch('/api/analytics',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({event_type:'whatsapp_click',page:'/'})}).catch(()=>{})}>
              💬 WhatsApp
            </a>
            <a href="mailto:Exaucejoel29@gmail.com" className="btn-outline">✉️ Exaucejoel29@gmail.com</a>
          </div>
        </div>
      </section>

      <footer>
        <div className="footer-brand">EX.AUCÉ</div>
        <div style={{display:'flex',gap:'2rem'}}>
          <Link href="/cv" style={{fontSize:'.75rem',color:'var(--muted)',textDecoration:'none',letterSpacing:'.06em'}}>CV & Tarifs</Link>
          <a href="https://wa.me/22901495140" target="_blank" rel="noopener noreferrer" style={{fontSize:'.75rem',color:'var(--muted)',textDecoration:'none',letterSpacing:'.06em'}}>WhatsApp</a>
          <a href="mailto:Exaucejoel29@gmail.com" style={{fontSize:'.75rem',color:'var(--muted)',textDecoration:'none',letterSpacing:'.06em'}}>Email</a>
        </div>
        <div className="footer-copy">© 2025 EXAUCÉ — Tous droits réservés</div>
      </footer>
    </>
  );
}
