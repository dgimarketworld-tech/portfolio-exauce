'use client';
import { useState, useEffect } from 'react';
import Link from 'next/link';

const DEFAULT_PROJECTS = [
  { id:1, title:'TechVibes Africa', type:'Thumbnail YouTube', stats:'Thumbnail optimisée · CTR amélioré', image_url:'/images/Projet 1.png', mockup_label:'TECHVIBES', bg_class:'port-bg-1', display_order:1 },
  { id:2, title:'FitNation Africa', type:'Montage Vidéo', stats:'Montage court · Rétention améliorée', image_url:'/images/projet 2.png', mockup_label:'FIT', bg_class:'port-bg-2', display_order:2 },
  { id:3, title:'BusinessAfrica.tv', type:'Site Web', stats:'Livré en 7 jours · Responsive · SEO de base', image_url:'/images/Projet 3.png', mockup_label:'WEB', bg_class:'port-bg-3', display_order:3 },
  { id:4, title:'King Kora Music', type:'Thumbnail + Montage', stats:'Thumbnail + montage · Résultats visibles', image_url:'/images/Projet 4.png', mockup_label:'KING', bg_class:'port-bg-4', display_order:4 },
  { id:5, title:'ModeBénin Shop', type:'Site Web E-commerce', stats:'Site e-commerce · Design mobile-first', image_url:null, mockup_label:'MODE', bg_class:'port-bg-5', display_order:5 },
  { id:6, title:'Lifestyle Béninoise', type:'Montage Vlog', stats:'Cuts dynamiques · Multi-formats', image_url:null, mockup_label:'VLOG', bg_class:'port-bg-6', display_order:6 },
];

export default function Home() {
  const [projects, setProjects] = useState(DEFAULT_PROJECTS);
  const [testimonials, setTestimonials] = useState([]);
  const [form, setForm] = useState({ name:'', email:'', message:'' });
  const [status, setStatus] = useState('idle');

  useEffect(() => {
    fetch('/api/projects').then(r=>r.json()).then(d=>{if(d?.length)setProjects(d);}).catch(()=>{});
    fetch('/api/testimonials').then(r=>r.json()).then(d=>{if(d)setTestimonials(d);}).catch(()=>{});
    fetch('/api/analytics',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({event_type:'page_view',page:'/'})}).catch(()=>{});
  }, []);

  useEffect(() => {
    const nav = document.getElementById('nav');
    const onScroll = () => nav?.classList.toggle('scrolled', window.scrollY > 48);
    window.addEventListener('scroll', onScroll, {passive:true});
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  useEffect(() => {
    const obs = new IntersectionObserver(entries => {
      entries.forEach(e => { if(e.isIntersecting){setTimeout(()=>e.target.classList.add('visible'),60);obs.unobserve(e.target);} });
    }, {threshold:0.07});
    document.querySelectorAll('.reveal').forEach(el=>obs.observe(el));
    return () => obs.disconnect();
  }, [projects, testimonials]);

  async function handleSubmit(e) {
    e.preventDefault();
    setStatus('sending');
    try {
      const res = await fetch('/api/contact',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(form)});
      if(res.ok){setStatus('sent');setForm({name:'',email:'',message:''});fetch('/api/analytics',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({event_type:'contact_submit',page:'/'})}).catch(()=>{});}
      else setStatus('error');
    } catch{setStatus('error');}
  }

  function playVideo(e) {
    const o = e.currentTarget.querySelector('.video-overlay');
    if(o) o.innerHTML='<p style="font-size:.72rem;letter-spacing:.14em;text-transform:uppercase;color:rgba(245,245,247,.4)">Ajoute ton lien YouTube dans /admin 🎬</p>';
  }

  return (
    <>
      {/* NAV */}
      <nav id="nav">
        <Link href="/" className="logo">EX<em>.</em>AUCÉ</Link>
        <ul className="nav-links">
          <li><a href="#about">À propos</a></li>
          <li><a href="#portfolio">Travaux</a></li>
          <li><a href="#services">Services</a></li>
          <li><Link href="/cv">CV & Tarifs</Link></li>
          <li><a href="#contact">Contact</a></li>
        </ul>
        <a href="#contact" className="btn-nav">Démarrer un projet</a>
      </nav>

      {/* HERO */}
      <section className="hero">
        <p className="hero-eyebrow">Développeur Web · Monteur Vidéo · Bénin</p>
        <h1>
          Du code<br/>
          qui <em>impacte.</em><br/>
          Du contenu<br/>
          qui <em>engage.</em>
        </h1>
        <p className="hero-sub">
          Je conçois des interfaces web modernes et des montages vidéo percutants —
          de la ligne de code au contenu qui convertit.
        </p>
        <div className="hero-ctas">
          <a href="#contact" className="btn-primary">Démarrer un projet</a>
          <a href="#portfolio" className="btn-ghost">Voir mon travail</a>
        </div>
        <div className="hero-scroll"><span>Scroll</span></div>
      </section>

      {/* STATS */}
      <div className="stats">
        {[
          ['20+','Projets livrés'],
          ['3 ans','Expérience'],
          ['100%','Délais respectés'],
          ['5★','Satisfaction'],
        ].map(([n,l])=>(
          <div key={l} className="stat reveal">
            <div className="stat-n">{n}</div>
            <div className="stat-l">{l}</div>
          </div>
        ))}
      </div>

      {/* ABOUT */}
      <section className="about" id="about">
        <div className="about-img reveal">
          <img src="/images/Exaucé.png" alt="Exaucé"/>
        </div>
        <div className="about-content reveal">
          <span className="label label-blue">À propos</span>
          <h2 className="about-title">Code propre.<br/>Contenu qui <span style={{color:'var(--blue)'}}>engage.</span></h2>
          <p className="about-desc">Développeur web front-end avec une forte sensibilité créative. Je construis des interfaces modernes en HTML, CSS, JavaScript, PHP et React — et en parallèle je réalise des montages vidéo percutants avec CapCut, Premiere Pro et After Effects.</p>
          <p className="about-desc">Autodidacte depuis 2021. Je couvre la chaîne créative de A à Z, du code au contenu. Chaque projet, traité avec rigueur et sans compromis sur la qualité.</p>
          <div className="tags">
            {['HTML · CSS · JS','PHP · React','WordPress','Montage Vidéo','Thumbnails','Canva · CapCut','Premiere Pro'].map(t=><span key={t} className="tag">{t}</span>)}
          </div>
        </div>
      </section>

      {/* VIDEO */}
      <section className="video-section" id="video">
        <div className="video-head reveal">
          <span className="label">Showreel</span>
          <h2 className="section-title">Ce que<br/>je fais.</h2>
        </div>
        <div className="video-wrap reveal" onClick={playVideo}>
          <div className={`port-bg-1`} style={{position:'absolute',inset:0,opacity:.9}}/>
          <div style={{position:'absolute',top:'12%',left:'6%',fontFamily:'Syne,sans-serif',fontSize:'clamp(2rem,6vw,5rem)',fontWeight:800,letterSpacing:'-.02em',opacity:.05}}>EXAUCÉ</div>
          <div className="video-overlay">
            <div className="play-ring">
              <svg width="22" height="22" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
            </div>
            <span className="video-label">Showreel 2025</span>
          </div>
        </div>
        <div className="video-meta">
          {[['Durée','1 min 48'],['Projets','8 réalisations'],['Mise à jour','Juin 2025'],['Outils','CapCut · Premiere Pro']].map(([k,v])=>(
            <div key={k} className="vm-item"><strong>{k}</strong>{v}</div>
          ))}
        </div>
      </section>

      {/* PORTFOLIO */}
      <section className="portfolio" id="portfolio">
        <div className="portfolio-head reveal">
          <span className="label">Portfolio</span>
          <h2 className="section-title">Travaux<br/>récents.</h2>
        </div>
        <div className="portfolio-grid">
          {projects.map(p=>(
            <div key={p.id} className="p-item reveal">
              {p.image_url
                ? <img src={p.image_url} alt={p.title}/>
                : <div className={p.bg_class} style={{position:'absolute',inset:0}}/>
              }
              {p.image_url && <div className="p-overlay"/>}
              <div className="p-mock">{p.mockup_label}</div>
              <div className="p-info">
                <div className="p-type">{p.type}</div>
                <div className="p-name">{p.title}</div>
                <div className="p-stat">{p.stats}</div>
              </div>
            </div>
          ))}
        </div>
      </section>

      {/* PROCESS */}
      <section className="process" id="process">
        {[
          {n:'01',t:'Accord & Paiement',d:"On valide l'idée, tu règles pour bloquer ta place."},
          {n:'02',t:'Envoi des fichiers',d:"Tu m'envoies rushs, photos et logos — par WhatsApp ou lien."},
          {n:'03',t:'Création',d:"Je m'occupe de tout : montage, design, code, optimisation."},
          {n:'04',t:'Livraison',d:"Tu reçois ton contenu final, prêt à publier ou déployer."},
        ].map(s=>(
          <div key={s.n} className="p-step reveal">
            <div className="p-num">{s.n}</div>
            <div className="p-dot"/>
            <div className="p-title">{s.t}</div>
            <p className="p-text">{s.d}</p>
          </div>
        ))}
      </section>

      {/* SERVICES */}
      <section className="services" id="services">
        <div className="services-head">
          <div className="reveal">
            <span className="label">Services</span>
            <h2 className="section-title">Ce que<br/>je fais<br/>pour toi.</h2>
          </div>
          <p className="reveal" style={{fontSize:'.9rem',color:'var(--muted)',lineHeight:1.8,maxWidth:'380px'}}>
            Du montage vidéo au site web en passant par les visuels — je couvre tout ce dont tu as besoin pour exister en ligne et convertir.
          </p>
        </div>
        <div className="services-grid">
          {[
            {n:'01',name:'Montage Vidéo',desc:'Cuts précis, sous-titres, musique, motion — CapCut, InShot, Premiere Pro. Formats YouTube, TikTok, Instagram, Facebook.'},
            {n:'02',name:'Visuels & Thumbnails',desc:'Miniatures percutantes, affiches, graphismes animés sur Canva. Livraison 48h, retouches incluses.'},
            {n:'03',name:'Sites Web',desc:"Landing pages, sites vitrines, plateformes en HTML/CSS/JS, PHP ou WordPress. Responsive, propre, dans les délais."},
            {n:'04',name:'Pack Créateur',desc:'Vidéo + visuels + suivi mensuel. L\'offre complète pour créateurs qui veulent avancer sans se disperser.'},
          ].map(s=>(
            <div key={s.n} className="s-card reveal">
              <div className="s-num">{s.n}</div>
              <div className="s-name">{s.name}</div>
              <p className="s-desc">{s.desc}</p>
            </div>
          ))}
        </div>
      </section>

      {/* TESTIMONIALS */}
      {testimonials.length > 0 && (
        <section className="testimonials-section" id="testimonials">
          <div className="reveal">
            <span className="label">Témoignages</span>
            <h2 className="section-title">Ce qu'ils<br/>disent.</h2>
          </div>
          <div className="t-grid">
            {testimonials.map(t=>(
              <div key={t.id} className="t-card reveal">
                <div className="t-stars">{'★'.repeat(Number(t.rating))}</div>
                <p className="t-msg">"{t.message}"</p>
                <div className="t-auth">
                  <div className="t-name">{t.name}</div>
                  <div className="t-role">{t.role}</div>
                </div>
              </div>
            ))}
          </div>
        </section>
      )}

      {/* CONTACT */}
      <section className="contact" id="contact">
        <div className="contact-inner">
          <span className="label label-blue reveal">Travaillons ensemble</span>
          <h2 className="contact-title reveal">Une idée ?<br/>Parlons-en.</h2>
          <p className="contact-sub reveal">Je réponds dans les 24h. Pas de modèles génériques — chaque projet est unique.</p>
          <form onSubmit={handleSubmit}>
            <div className="f-row reveal">
              <input type="text" placeholder="Ton nom" required value={form.name} onChange={e=>setForm({...form,name:e.target.value})}/>
            </div>
            <div className="f-row reveal">
              <input type="email" placeholder="Ton email" required value={form.email} onChange={e=>setForm({...form,email:e.target.value})}/>
            </div>
            <div className="f-row reveal">
              <textarea placeholder="Ton projet en quelques mots..." value={form.message} onChange={e=>setForm({...form,message:e.target.value})}/>
            </div>
            <div className="f-row reveal" style={{marginTop:'1.5rem'}}>
              <button type="submit" className="btn-primary" disabled={status==='sending'}
                style={status==='sent'?{background:'#0d7a45',borderRadius:'980px'}:{}}>
                {status==='idle'&&'Envoyer le message'}
                {status==='sending'&&'Envoi en cours...'}
                {status==='sent'&&'Message envoyé ✓'}
                {status==='error'&&'Erreur — réessaie'}
              </button>
              {status==='sent'&&<p style={{marginTop:'1rem',fontSize:'.82rem',color:'var(--blue)'}}>Je reviens vers toi dans les 24h.</p>}
            </div>
          </form>
          <div className="contact-links reveal">
            <a href="https://wa.me/22901495140" target="_blank" rel="noopener noreferrer" className="c-link"
               onClick={()=>fetch('/api/analytics',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({event_type:'whatsapp_click',page:'/'})}).catch(()=>{})}>
              💬 WhatsApp
            </a>
            <a href="mailto:Exaucejoel29@gmail.com" className="c-link">✉ Exaucejoel29@gmail.com</a>
            <Link href="/cv" className="c-link">📄 CV & Tarifs</Link>
          </div>
        </div>
      </section>

      {/* FOOTER */}
      <footer>
        <Link href="/" className="footer-logo">EX<em>.</em>AUCÉ</Link>
        <div className="footer-links">
          <Link href="/cv">CV & Tarifs</Link>
          <a href="https://wa.me/22901495140" target="_blank" rel="noopener noreferrer">WhatsApp</a>
          <a href="mailto:Exaucejoel29@gmail.com">Email</a>
        </div>
        <span className="footer-copy">© 2025 EXAUCÉ</span>
      </footer>
    </>
  );
}
