import { sql } from '@vercel/postgres';
import { NextResponse } from 'next/server';

export async function GET() {
  try {
    const { rows } = await sql`SELECT * FROM testimonials ORDER BY created_at DESC LIMIT 6`;
    return NextResponse.json(rows);
  } catch {
    return NextResponse.json([]);
  }
}

export async function POST(req) {
  try {
    const { name, role, message, rating } = await req.json();
    if (!name || !message) return NextResponse.json({ error: 'Champs requis' }, { status: 400 });
    const { rows } = await sql`
      INSERT INTO testimonials (name, role, message, rating)
      VALUES (${name}, ${role||''}, ${message}, ${rating||5})
      RETURNING *
    `;
    return NextResponse.json(rows[0]);
  } catch {
    return NextResponse.json({ error: 'Erreur serveur' }, { status: 500 });
  }
}

export async function DELETE(req) {
  try {
    const { id } = await req.json();
    await sql`DELETE FROM testimonials WHERE id = ${id}`;
    return NextResponse.json({ success: true });
  } catch {
    return NextResponse.json({ error: 'Erreur serveur' }, { status: 500 });
  }
}
