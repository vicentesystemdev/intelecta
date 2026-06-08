import { Head, Link } from '@inertiajs/react';

export default function Welcome({ auth }) {
    return (
        <>
            <Head title="INTELECTA" />

            <div className="min-h-screen bg-slate-50 text-slate-900">
                <div className="mx-auto flex min-h-screen max-w-6xl flex-col justify-center px-6 py-16">
                    <header className="mb-12 rounded-[2rem] border border-slate-200 bg-white/95 p-8 shadow-xl shadow-slate-200/40 backdrop-blur-sm sm:px-12 lg:flex lg:items-center lg:justify-between">
                        <div className="max-w-3xl">
                            <p className="text-sm font-semibold uppercase tracking-[0.35em] text-sky-600">
                                INTELECTA
                            </p>
                            <h1 className="mt-4 text-4xl font-semibold leading-tight text-slate-950 sm:text-5xl">
                                Plataforma académica para gestionar cursos, permisos y proyectos.
                            </h1>
                            <p className="mt-5 max-w-2xl text-base leading-7 text-slate-600 sm:text-lg">
                                Una landing limpia y profesional para tu sistema Laravel con Breeze, Inertia, Spatie Permission y shadcn/ui.
                            </p>
                        </div>

                        <div className="mt-8 flex flex-wrap gap-3 sm:mt-0">
                            {auth.user ? (
                                <Link
                                    href={route('dashboard')}
                                    className="inline-flex items-center justify-center rounded-full bg-sky-600 px-5 py-3 text-sm font-semibold text-white shadow-sm shadow-sky-600/20 transition hover:bg-sky-700"
                                >
                                    Ir al panel
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href={route('login')}
                                        className="inline-flex items-center justify-center rounded-full border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-900 transition hover:border-slate-400 hover:bg-slate-50"
                                    >
                                        Acceder
                                    </Link>
                                    <Link
                                        href={route('register')}
                                        className="inline-flex items-center justify-center rounded-full bg-sky-600 px-5 py-3 text-sm font-semibold text-white shadow-sm shadow-sky-600/20 transition hover:bg-sky-700"
                                    >
                                        Regístrate
                                    </Link>
                                </>
                            )}
                        </div>
                    </header>

                    <section className="grid gap-6 lg:grid-cols-3">
                        <article className="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm shadow-slate-200/30">
                            <h2 className="text-xl font-semibold text-slate-950">Gestión académica</h2>
                            <p className="mt-3 text-sm leading-6 text-slate-600">
                                Controla roles y accesos con una experiencia clara y profesional, orientada a entornos educativos.
                            </p>
                        </article>
                        <article className="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm shadow-slate-200/30">
                            <h2 className="text-xl font-semibold text-slate-950">React + Inertia</h2>
                            <p className="mt-3 text-sm leading-6 text-slate-600">
                                Navegación fluida con renderizado SPA y una interfaz moderna que no sacrifica la estabilidad de Laravel.
                            </p>
                        </article>
                        <article className="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm shadow-slate-200/30">
                            <h2 className="text-xl font-semibold text-slate-950">Compilación confiable</h2>
                            <p className="mt-3 text-sm leading-6 text-slate-600">
                                Vite y Tailwind integrados para un build rápido y limpio, sin dependencias forzadas ni legacy peer deps.
                            </p>
                        </article>
                    </section>

                    <section className="mt-10 rounded-[2rem] bg-sky-600 px-8 py-10 text-white shadow-xl shadow-sky-700/20 sm:px-12">
                        <h2 className="text-2xl font-semibold">Diseño académico y funcional</h2>
                        <p className="mt-4 max-w-3xl text-base leading-7 text-sky-100">
                            Un ambiente visual sobrio que utiliza azul, índigo, celeste, blanco y gris claro, ideal para proyectos educativos y administrativas.
                        </p>
                    </section>
                </div>
            </div>
        </>
    );
}
