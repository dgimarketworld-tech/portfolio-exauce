import { sql } from '@vercel/postgres';
import { NextResponse } from 'next/server';

const PROJECTS = [
  { title:'TechVibes Africa', type:'Thumbnail YouTube', stats:'Thumbnail optimisée · CTR amélioré', image_url:'/images/Projet 1.png', mockup_label:'TECHVIBES', bg_class:'port-bg-1', display_order:1 },
  { title:'FitNation Africa', type:'Montage Vidéo', stats:'Montage court · Rétention améliorée', image_url:'/images/projet 2.png', mockup_label:'FIT', bg_class:'port-bg-2', display_order:2 },
  { title:'BusinessAfrica.tv', type:'Site Web', stats:'Livré en 7 jours · Responsive · SEO de base', image_url:'/images/Projet 3.png', mockup_label:'WEB', bg_class:'port-bg-3', display_order:3 },
  { title:'King Kora Music', type:'Thumbnail + Montage', stats:'Thumbnail + montage · Résultats visibles', image_url:'/images/Projet 4.png', mockup_label:'KING', bg_class:'port-bg-4', display_order:4 },
  { title:'ModeBénin Shop', type:'Site Web E-commerce', stats:'Site e-commerce livré · Design mobile-first', image_url:null, mockup_label:'MODE', bg_class:'port-bg-5', display_order:5 },
  { title:'Lifestyle Béninoise', type:'Montage Vlog', stats:'Montage vlog · Cuts dynamiques · Multi-formats', image_url:null, mockup_label:'VLOG', bg_class:'port-bg-6', display_order:6 },
];

export async function POST() {
  try {
    await sql`CREATE TABLE IF NOT EXISTS contacts (id SERIAL PRIMARY KEY, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, message TEXT, created_at TIMESTAMPTZ DEFAULT NOW())`;
    await sql`CREATE TABLE IF NOT EXISTS projects (id SERIAL PRIMARY KEY, title VARCHAR(255) NOT NULL, type VARCHAR(100), stats VARCHAR(255), image_url VARCHAR(500), mockup_label VARCHAR(50), bg_class VARCHAR(50), display_order INTEGER DEFAULT 0, created_at TIMESTAMPTZ DEFAULT NOW())`;
    await sql`CREATE TABLE IF NOT EXISTS testimonials (id SERIAL PRIMARY KEY, name VARCHAR(255) NOT NULL, role VARCHAR(255), message TEXT NOT NULL, rating INTEGER DEFAULT 5, created_at TIMESTAMPTZ DEFAULT NOW())`;
    await sql`CREATE TABLE IF NOT EXISTS analytics (id SERIAL PRIMARY KEY, event_type VARCHAR(100) NOT NULL, page VARCHAR(255), created_at TIMESTAMPTZ DEFAULT NOW())`;

    const { rows: existing } = await sql`SELECT COUNT(*) as count FROM projects`;
    if (Number(existing[0].count) === 0) {
      for (const p of PROJECTS) {
        await sql`INSERT INTO projects (title, type, stats, image_url, mockup_label, bg_class, display_order) VALUES (${p.title}, ${p.type}, ${p.stats}, ${p.image_url}, ${p.mockup_label}, ${p.bg_class}, ${p.display_order})`;
      }
    }
    return NextResponse.json({ success: true, message: 'DB initialisée' });
  } catch (err) {
    return NextResponse.json({ error: err.message }, { status: 500 });
  }
}
