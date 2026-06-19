'use client';
import { useState, useEffect } from 'react';

const s = {
  page: { minHeight:'100vh', background:'#060606', color:'#f4f3ef', fontFamily:'DM Sans,sans-serif', padding:'2rem 3%' },
  title: { fontFamily:'Bebas Neue,sans-serif', fontSize:'2.5rem', letterSpacing:'.08em', marginBottom:'2rem', color:'#f4f3ef' },
  card: { background:'#0e0e0e', border:'1px solid rgba(244,243,239,.09)', padding:'2rem', marginBottom:'1.5rem' },
  cardTitle: { fontSize:'.65rem', letterSpacing:'.2em', textTransform:'uppercase', color:'#1a5cff', marginBottom:'1.2rem' },
  grid4: { display:'grid', gridTemplateColumns:'repeat(4,1fr)', gap:'1px', background:'rgba(244,243,239,.09)', marginBottom:'2rem' },
  statBox: { background:'#060606', padding:'1.5rem', textAlign:'center' },
  statNum: { fontFamily:'Bebas Neue,sans-serif', fontSize:'2.5rem', color:'#f4f3ef', lineHeight:1 },
  statLbl: { fontSize:'.68rem', color:'rgba(244,243,239,.42)', letterSpacing:'.12em', textTransform:'uppercase', marginTop:'.3rem' },
  input: { width:'100%', background:'transparent', border:'none', borderBottom:'1px solid rgba(244,243,239,.15)', padding:'.8rem 0', color:'#f4f3ef', fontFamily:'DM Sans,sans-serif', fontSize:'.88rem', outline:'none', marginBottom:'.8rem' },
  btn: { background:'#1a5cff', color:'#fff', border:'none', padding:'.6rem 1.4rem', cursor:'pointer', fontFamily:'DM Sans,sans-serif', fontSize:'.82rem', fontWeight:500, marginRight:'.5rem' },
  btnRed: { background:'#7a0d0d', color:'#fff', border:'none', padding:'.4rem .9rem', cursor:'pointer', fontFamily:'DM Sans,sans-serif', fontSize:'.75rem' },
  table: { width:'100%', borderCollapse:'collapse', fontSize:'.8rem' },
  th: { padding:'.6rem 1rem', textAlign:'left', borderBottom:'1px solid rgba(244,243,239,.09)', color:'rgba(244,243,239,.42)', fontSize:'.65rem', letterSpacing:'.12em', textTransform:'uppercase' },
  td: { padding:'.75rem 1rem', borderBottom:'1px solid rgba(244,243,239,.06)', color:'rgba(244,243,239,.75)', verticalAlign:'top' },
  badge: { background:'rgba(26,92,255,.15)', color:'#1a5cff', padding:'.15rem .5rem', fontSize:'.65rem', letterSpacing:'.06em' },
  tabs: { display:'flex', gap:0, marginBottom:'2rem', borderBottom:'1px solid rgba(244,243,239,.09)' },
  tab: { padding:'.75rem 1.5rem', cursor:'pointer', fontSize:'.75rem', letterSpacing:'.12em', textTransform:'uppercase', background:'none', border:'none', color:'rgba(244,243,239,.4)', transition:'color .2s' },
  tabA: { padding:'.75rem 1.5rem', cursor:'pointer', fontSize:'.75rem', letterSpacing:'.12em', textTransform:'uppercase', background:'none', border:'none', borderBottom:'2px solid #1a5cff', color:'#f4f3ef', marginBottom:'-1px' },
};

export default function Admin() {
  const [auth, setAuth] = useState(false);
  const [pwd, setPwd] = useState('');
  const [tab, setTab] = useState('stats');
  const [stats, setStats] = useState({ page_views:0, contact_submits:0, whatsapp_clicks:0, cv_downloads:0 });
  const [contacts, setContacts] = useState([]);
  const [projects, setProjects] = useState([]);
  const [testimonials, setTestimonials] = useState([]);
  const [newProject, setNewProject] = useState({ title:'', type:'', stats:'', image_url:'', mockup_label:'', bg_class:'port-bg-1', display_order:'' });
  const [newTestimonial, setNewTestimonial] = useState({ name:'', role:'', message:'', rating:5 });
  const [msg, setMsg] = useState('');

  const PASS = process.env.NEXT_PUBLIC_ADMIN_PASSWORD || 'admin2025';

  function login(e) {
    e.preventDefault();
    if (pwd === PASS) { setAuth(true); loadAll(); }
    else setMsg('Mot de passe incorrect');
  }

  async function loadAll() {
    const [s, c, p, t] = await Promise.all([
      fetch('/api/analytics').then(r => r.json()),
      fetch('/api/contact').then(r => r.json()),
      fetch('/api/projects').then(r => r.json()),
      fetch('/api/testimonials').then(r => r.json()),
    ]);
    if (s) setStats(s);
    if (Array.isArray(c)) setContacts(c);
    if (Array.isArray(p)) setProjects(p);
    if (Array.isArray(t)) setTestimonials(t);
  }

  async function addProject(e) {
    e.preventDefault();
    const res = await fetch('/api/projects', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({...newProject, display_order: Number(newProject.display_order)||0}) });
    if (res.ok) { setNewProject({title:'',type:'',stats:'',image_url:'',mockup_label:'',bg_class:'port-bg-1',display_order:''}); loadAll(); setMsg('Projet ajouté ✓'); }
  }

  async function deleteProject(id) {
    if (!confirm('Supprimer ce projet ?')) return;
    await fetch('/api/projects', { method:'DELETE', headers:{'Content-Type':'application/json'}, body: JSON.stringify({id}) });
    loadAll();
  }

  async function addTestimonial(e) {
    e.preventDefault();
    const res = await fetch('/api/testimonials', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(newTestimonial) });
    if (res.ok) { setNewTestimonial({name:'',role:'',message:'',rating:5}); loadAll(); setMsg('Témoignage ajouté ✓'); }
  }

  async function deleteTestimonial(id) {
    if (!confirm('Supprimer ce témoignage ?')) return;
    await fetch('/api/testimonials', { method:'DELETE', headers:{'Content-Type':'application/json'}, body: JSON.stringify({id}) });
    loadAll();
  }

  async function initDB() {
    const res = await fetch('/api/seed', { method:'POST' });
    const d = await res.json();
    setMsg(d.message || d.error);
    loadAll();
  }

  if (!auth) return (
    <div style={{...s.page, display:'flex', alignItems:'center', justifyContent:'center'}}>
      <div style={{width:360}}>
        <div style={s.title}>ADMIN</div>
        <form onSubmit={login}>
          <input style={s.input} type="password" placeholder="Mot de passe admin" value={pwd} onChange={e => setPwd(e.target.value)} autoFocus />
          {msg && <p style={{color:'#ff4444',fontSize:'.8rem',marginBottom:'1rem'}}>{msg}</p>}
          <button type="submit" style={s.btn}>Connexion</button>
        </form>
      </div>
    </div>
  );

  return (
    <div style={s.page}>
      <div style={{display:'flex',justifyContent:'space-between',alignItems:'center',marginBottom:'2rem'}}>
        <div style={s.title}>DASHBOARD ADMIN</div>
        <div style={{display:'flex',gap:'1rem'}}>
          <button style={s.btn} onClick={initDB}>Init DB</button>
          <button style={{...s.btn,background:'transparent',border:'1px solid rgba(244,243,239,.2)'}} onClick={() => setAuth(false)}>Déconnexion</button>
        </div>
      </div>
      {msg && <div style={{background:'rgba(26,92,255,.1)',border:'1px solid rgba(26,92,255,.3)',padding:'.75rem 1rem',marginBottom:'1.5rem',fontSize:'.82rem',color:'#1a5cff'}}>{msg}</div>}

      <div style={s.tabs}>
        {['stats','contacts','projets','témoignages'].map(t => (
          <button key={t} style={tab===t ? s.tabA : s.tab} onClick={() => setTab(t)}>{t}</button>
        ))}
      </div>

      {tab === 'stats' && (
        <>
          <div style={s.grid4}>
            <div style={s.statBox}><div style={s.statNum}>{stats.page_views}</div><div style={s.statLbl}>vues de page</div></div>
            <div style={s.statBox}><div style={s.statNum}>{stats.contact_submits}</div><div style={s.statLbl}>messages reçus</div></div>
            <div style={s.statBox}><div style={s.statNum}>{stats.whatsapp_clicks}</div><div style={s.statLbl}>clics WhatsApp</div></div>
            <div style={s.statBox}><div style={s.statNum}>{stats.cv_downloads}</div><div style={s.statLbl}>téléch. CV</div></div>
          </div>
          <div style={s.grid4}>
            <div style={s.statBox}><div style={s.statNum}>{contacts.length}</div><div style={s.statLbl}>contacts total</div></div>
            <div style={s.statBox}><div style={s.statNum}>{projects.length}</div><div style={s.statLbl}>projets en ligne</div></div>
            <div style={s.statBox}><div style={s.statNum}>{testimonials.length}</div><div style={s.statLbl}>témoignages</div></div>
            <div style={s.statBox}><div style={s.statNum}>↗</div><div style={s.statLbl}>tout va bien</div></div>
          </div>
        </>
      )}

      {tab === 'contacts' && (
        <div style={s.card}>
          <div style={s.cardTitle}>Messages reçus ({contacts.length})</div>
          <table style={s.table}>
            <thead><tr><th style={s.th}>Nom</th><th style={s.th}>Email</th><th style={s.th}>Message</th><th style={s.th}>Date</th></tr></thead>
            <tbody>
              {contacts.map(c => (
                <tr key={c.id}>
                  <td style={s.td}>{c.name}</td>
                  <td style={s.td}><a href={`mailto:${c.email}`} style={{color:'#1a5cff',textDecoration:'none'}}>{c.email}</a></td>
                  <td style={s.td}>{c.message}</td>
                  <td style={{...s.td,whiteSpace:'nowrap',fontSize:'.72rem'}}>{new Date(c.created_at).toLocaleDateString('fr-FR')}</td>
                </tr>
              ))}
              {contacts.length === 0 && <tr><td colSpan={4} style={{...s.td,textAlign:'center',opacity:.4}}>Aucun message pour l'instant</td></tr>}
            </tbody>
          </table>
        </div>
      )}

      {tab === 'projets' && (
        <>
          <div style={s.card}>
            <div style={s.cardTitle}>Ajouter un projet</div>
            <form onSubmit={addProject}>
              <div style={{display:'grid',gridTemplateColumns:'1fr 1fr',gap:'0 2rem'}}>
                <input style={s.input} placeholder="Titre *" required value={newProject.title} onChange={e => setNewProject({...newProject,title:e.target.value})} />
                <input style={s.input} placeholder="Type (ex: Site Web)" value={newProject.type} onChange={e => setNewProject({...newProject,type:e.target.value})} />
                <input style={s.input} placeholder="Stats / description courte" value={newProject.stats} onChange={e => setNewProject({...newProject,stats:e.target.value})} />
                <input style={s.input} placeholder="URL image (ex: /images/monprojet.png)" value={newProject.image_url} onChange={e => setNewProject({...newProject,image_url:e.target.value})} />
                <input style={s.input} placeholder="Label mockup (ex: WEB)" value={newProject.mockup_label} onChange={e => setNewProject({...newProject,mockup_label:e.target.value})} />
                <select style={{...s.input,marginBottom:'.8rem'}} value={newProject.bg_class} onChange={e => setNewProject({...newProject,bg_class:e.target.value})}>
                  {['port-bg-1','port-bg-2','port-bg-3','port-bg-4','port-bg-5','port-bg-6'].map(b => <option key={b} value={b}>{b}</option>)}
                </select>
                <input style={s.input} type="number" placeholder="Ordre d'affichage" value={newProject.display_order} onChange={e => setNewProject({...newProject,display_order:e.target.value})} />
              </div>
              <button type="submit" style={s.btn}>Ajouter le projet</button>
            </form>
          </div>
          <div style={s.card}>
            <div style={s.cardTitle}>Projets en ligne ({projects.length})</div>
            <table style={s.table}>
              <thead><tr><th style={s.th}>#</th><th style={s.th}>Titre</th><th style={s.th}>Type</th><th style={s.th}>Stats</th><th style={s.th}></th></tr></thead>
              <tbody>
                {projects.map(p => (
                  <tr key={p.id}>
                    <td style={{...s.td,opacity:.4}}>{p.display_order}</td>
                    <td style={s.td}>{p.title}</td>
                    <td style={s.td}><span style={s.badge}>{p.type}</span></td>
                    <td style={{...s.td,fontSize:'.72rem'}}>{p.stats}</td>
                    <td style={s.td}><button style={s.btnRed} onClick={() => deleteProject(p.id)}>Supprimer</button></td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </>
      )}

      {tab === 'témoignages' && (
        <>
          <div style={s.card}>
            <div style={s.cardTitle}>Ajouter un témoignage</div>
            <form onSubmit={addTestimonial}>
              <div style={{display:'grid',gridTemplateColumns:'1fr 1fr',gap:'0 2rem'}}>
                <input style={s.input} placeholder="Nom *" required value={newTestimonial.name} onChange={e => setNewTestimonial({...newTestimonial,name:e.target.value})} />
                <input style={s.input} placeholder="Rôle / Projet" value={newTestimonial.role} onChange={e => setNewTestimonial({...newTestimonial,role:e.target.value})} />
              </div>
              <textarea style={{...s.input,minHeight:'80px',resize:'vertical'}} placeholder="Message *" required value={newTestimonial.message} onChange={e => setNewTestimonial({...newTestimonial,message:e.target.value})} />
              <div style={{marginBottom:'1rem'}}>
                <label style={{fontSize:'.75rem',color:'rgba(244,243,239,.42)',marginRight:'1rem'}}>Note :</label>
                {[5,4,3,2,1].map(r => (
                  <button key={r} type="button" style={{background:'none',border:'none',cursor:'pointer',color:newTestimonial.rating>=r?'#1a5cff':'rgba(244,243,239,.2)',fontSize:'1.2rem',padding:'0 .15rem'}} onClick={() => setNewTestimonial({...newTestimonial,rating:r})}>★</button>
                ))}
              </div>
              <button type="submit" style={s.btn}>Ajouter le témoignage</button>
            </form>
          </div>
          <div style={s.card}>
            <div style={s.cardTitle}>Témoignages ({testimonials.length})</div>
            <table style={s.table}>
              <thead><tr><th style={s.th}>Nom</th><th style={s.th}>Rôle</th><th style={s.th}>Message</th><th style={s.th}>Note</th><th style={s.th}></th></tr></thead>
              <tbody>
                {testimonials.map(t => (
                  <tr key={t.id}>
                    <td style={s.td}>{t.name}</td>
                    <td style={{...s.td,fontSize:'.72rem'}}>{t.role}</td>
                    <td style={s.td}>{t.message}</td>
                    <td style={{...s.td,color:'#1a5cff'}}>{'★'.repeat(Number(t.rating))}</td>
                    <td style={s.td}><button style={s.btnRed} onClick={() => deleteTestimonial(t.id)}>Supprimer</button></td>
                  </tr>
                ))}
                {testimonials.length === 0 && <tr><td colSpan={5} style={{...s.td,textAlign:'center',opacity:.4}}>Aucun témoignage</td></tr>}
              </tbody>
            </table>
          </div>
        </>
      )}
    </div>
  );
}
