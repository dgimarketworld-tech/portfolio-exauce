import { sql } from '@vercel/postgres';
import { NextResponse } from 'next/server';

export async function GET() {
  try {
    const { rows } = await sql`SELECT * FROM projects ORDER BY display_order ASC`;
    return NextResponse.json(rows);
  } catch {
    return NextResponse.json([]);
  }
}

export async function POST(req) {
  try {
    const { title, type, stats, image_url, mockup_label, bg_class, display_order } = await req.json();
    if (!title) return NextResponse.json({ error: 'Titre requis' }, { status: 400 });
    const { rows } = await sql`
      INSERT INTO projects (title, type, stats, image_url, mockup_label, bg_class, display_order)
      VALUES (${title}, ${type||''}, ${stats||''}, ${image_url||null}, ${mockup_label||''}, ${bg_class||'port-bg-1'}, ${display_order||0})
      RETURNING *
    `;
    return NextResponse.json(rows[0]);
  } catch (err) {
    return NextResponse.json({ error: 'Erreur serveur' }, { status: 500 });
  }
}

export async function DELETE(req) {
  try {
    const { id } = await req.json();
    await sql`DELETE FROM projects WHERE id = ${id}`;
    return NextResponse.json({ success: true });
  } catch {
    return NextResponse.json({ error: 'Erreur serveur' }, { status: 500 });
  }
}
