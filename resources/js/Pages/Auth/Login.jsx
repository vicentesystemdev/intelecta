import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import ThemeToggle from '@/Components/ThemeToggle';
import TextInput from '@/Components/TextInput';
import useTheme from '@/Hooks/useTheme';
import { Head, Link, useForm } from '@inertiajs/react';
import React, { useState } from 'react';
import {
    GraduationCap,
    Users,
    FileQuestion,
    BookCopy,
    TrendingUp,
    ArrowLeft,
    ArrowRight,
    Mail,
    Lock
} from 'lucide-react';

export default function Login({ status, canResetPassword }) {
    useTheme();
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const [imgError, setImgError] = useState(false);

    const submit = (e) => {
        e.preventDefault();

        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <>
            <Head title="Iniciar Sesión - INTELECTA" />

            <div className="flex min-h-screen bg-slate-50 font-sans text-slate-900 selection:bg-indigo-500 selection:text-white dark:bg-slate-950 dark:text-slate-100">
                {/* Columna Izquierda: Panel Visual Institucional */}
                <div className="hidden lg:flex lg:w-1/2 bg-gradient-to-tr from-indigo-950 via-indigo-900 to-cyan-900 p-12 text-white flex-col justify-between relative overflow-hidden">
                    {/* Patrón de fondo abstracto */}
                    <div className="absolute inset-0 bg-[linear-gradient(to_right,#ffffff05_1px,transparent_1px),linear-gradient(to_bottom,#ffffff05_1px,transparent_1px)] bg-[size:24px_24px] pointer-events-none" />
                    
                    {/* Luces de acento */}
                    <div className="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-cyan-500/10 blur-3xl pointer-events-none" />
                    <div className="absolute -left-20 -bottom-20 h-80 w-80 rounded-full bg-indigo-500/10 blur-3xl pointer-events-none" />

                    {/* Logo Superior */}
                    <div className="relative z-10 flex items-center gap-3">
                        <span className="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/10 text-white backdrop-blur-md border border-white/10">
                            <GraduationCap className="h-6 w-6 text-cyan-300" />
                        </span>
                        <span className="text-2xl font-black tracking-[0.15em] text-white">
                            INTELECTA
                        </span>
                    </div>

                    {/* Contenido Central: Títulos y Vista Previa */}
                    <div className="relative z-10 max-w-xl my-auto py-10">
                        <h2 className="text-3xl sm:text-4xl font-extrabold tracking-tight text-white leading-tight">
                            Evaluación y análisis del desempeño lógico-matemático preuniversitario
                        </h2>
                        <p className="mt-4 text-sm text-indigo-200 leading-relaxed">
                            Acceso institucional para administrar postulantes, evaluaciones académicas, bancos de preguntas y seguimiento del desempeño.
                        </p>

                        {/* Imagen / Fallback Visual */}
                        <div className="mt-8 relative rounded-3xl overflow-hidden bg-white/5 border border-white/10 p-2 shadow-2xl">
                            {!imgError ? (
                                <img
                                    src="/images/landing/hero-dashboard.png"
                                    alt="Panel de control INTELECTA"
                                    onError={() => setImgError(true)}
                                    className="w-full h-56 object-cover rounded-2xl"
                                />
                            ) : (
                                <div className="h-56 bg-gradient-to-br from-indigo-950/60 to-cyan-950/60 rounded-2xl p-6 flex flex-col justify-between relative overflow-hidden">
                                    <div className="absolute inset-0 bg-[linear-gradient(to_right,#ffffff05_1px,transparent_1px),linear-gradient(to_bottom,#ffffff05_1px,transparent_1px)] bg-[size:16px_16px] pointer-events-none" />
                                    <div className="flex justify-between items-center border-b border-white/10 pb-3">
                                        <span className="text-[10px] font-bold text-cyan-300 tracking-wider uppercase">INTELECTA CONSOLE</span>
                                        <span className="h-2 w-2 rounded-full bg-emerald-400 animate-pulse" />
                                    </div>
                                    <div className="flex-1 flex flex-col justify-center my-4">
                                        <div className="flex items-end justify-between h-24 px-4">
                                            <div className="w-8 bg-gradient-to-t from-indigo-500/50 to-cyan-400 rounded-t h-16" />
                                            <div className="w-8 bg-gradient-to-t from-indigo-500/50 to-cyan-400 rounded-t h-20" />
                                            <div className="w-8 bg-gradient-to-t from-indigo-500/50 to-cyan-400 rounded-t h-10" />
                                            <div className="w-8 bg-gradient-to-t from-indigo-500/50 to-cyan-400 rounded-t h-24" />
                                            <div className="w-8 bg-gradient-to-t from-indigo-500/50 to-cyan-400 rounded-t h-14" />
                                        </div>
                                    </div>
                                    <p className="text-[10px] text-indigo-200">Consola de seguimiento predictivo y control académico.</p>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Tarjetas Visuales Pequeñas del Panel */}
                    <div className="relative z-10 grid grid-cols-2 gap-4">
                        {/* Tarjeta Postulantes */}
                        <div className="flex items-center gap-3 rounded-2xl border border-white/5 bg-white/5 p-4 backdrop-blur-sm">
                            <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-300 shrink-0">
                                <Users className="h-5 w-5" />
                            </span>
                            <div>
                                <p className="text-xs font-bold text-white">Postulantes</p>
                                <p className="text-[10px] text-indigo-200">Perfiles y procedencias</p>
                            </div>
                        </div>

                        {/* Tarjeta Evaluaciones */}
                        <div className="flex items-center gap-3 rounded-2xl border border-white/5 bg-white/5 p-4 backdrop-blur-sm">
                            <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-300 shrink-0">
                                <FileQuestion className="h-5 w-5" />
                            </span>
                            <div>
                                <p className="text-xs font-bold text-white">Evaluaciones</p>
                                <p className="text-[10px] text-indigo-200">Banco de reactivos</p>
                            </div>
                        </div>

                        {/* Tarjeta Plantillas */}
                        <div className="flex items-center gap-3 rounded-2xl border border-white/5 bg-white/5 p-4 backdrop-blur-sm">
                            <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-300 shrink-0">
                                <BookCopy className="h-5 w-5" />
                            </span>
                            <div>
                                <p className="text-xs font-bold text-white">Plantillas</p>
                                <p className="text-[10px] text-indigo-200">Diseño estructurado</p>
                            </div>
                        </div>

                        {/* Tarjeta Learning Analytics */}
                        <div className="flex items-center gap-3 rounded-2xl border border-white/5 bg-white/5 p-4 backdrop-blur-sm">
                            <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-300 shrink-0">
                                <TrendingUp className="h-5 w-5" />
                            </span>
                            <div>
                                <p className="text-xs font-bold text-white">Learning Analytics</p>
                                <p className="text-[10px] text-indigo-200">Seguimiento predictivo</p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Columna Derecha: Formulario de Login */}
                <div className="relative flex w-full flex-col items-center justify-center p-6 sm:p-12 md:p-20 lg:w-1/2">
                    <ThemeToggle className="absolute right-5 top-5 sm:right-8 sm:top-8" />
                    <div className="w-full max-w-md">
                        {/* Cabecera Móvil (sólo visible si no está en desktop) */}
                        <div className="flex items-center gap-3 lg:hidden mb-8 justify-center">
                            <span className="flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-600/20">
                                <GraduationCap className="h-6 w-6" />
                            </span>
                            <span className="bg-gradient-to-r from-slate-900 to-indigo-900 bg-clip-text text-2xl font-black tracking-[0.15em] text-transparent dark:from-slate-100 dark:to-indigo-300">
                                INTELECTA
                            </span>
                        </div>

                        {/* Form Card */}
                        <div className="rounded-3xl border border-slate-200/80 bg-white p-8 shadow-xl shadow-slate-200/40 dark:border-slate-800 dark:bg-slate-900 dark:shadow-slate-950/40 sm:p-10">
                            <div>
                                <h1 className="text-2xl font-bold tracking-tight text-slate-950 dark:text-slate-100">
                                    Iniciar sesión
                                </h1>
                                <p className="mt-1.5 text-sm text-slate-500 dark:text-slate-400">
                                    Accede al panel académico de INTELECTA.
                                </p>
                            </div>

                            {status && (
                                <div className="mt-4 rounded-xl border border-emerald-100 bg-emerald-50 p-4 text-xs font-medium text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                    {status}
                                </div>
                            )}

                            <form onSubmit={submit} className="mt-8 space-y-6">
                                {/* Email Field */}
                                <div>
                                    <InputLabel htmlFor="email" value="Correo electrónico" className="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300" />

                                    <div className="relative mt-1">
                                        <span className="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                                            <Mail className="h-4 w-4" />
                                        </span>
                                        <TextInput
                                            id="email"
                                            type="email"
                                            name="email"
                                            value={data.email}
                                            className="block w-full rounded-xl border-slate-200 bg-slate-50/50 pl-10 hover:border-slate-300 focus:border-indigo-500 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                                            autoComplete="username"
                                            isFocused={true}
                                            onChange={(e) => setData('email', e.target.value)}
                                        />
                                    </div>

                                    <InputError message={errors.email} className="mt-1.5 text-xs font-semibold text-red-600" />
                                </div>

                                {/* Password Field */}
                                <div>
                                    <InputLabel htmlFor="password" value="Contraseña" className="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300" />

                                    <div className="relative mt-1">
                                        <span className="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                                            <Lock className="h-4 w-4" />
                                        </span>
                                        <TextInput
                                            id="password"
                                            type="password"
                                            name="password"
                                            value={data.password}
                                            className="block w-full rounded-xl border-slate-200 bg-slate-50/50 pl-10 hover:border-slate-300 focus:border-indigo-500 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                                            autoComplete="current-password"
                                            onChange={(e) => setData('password', e.target.value)}
                                        />
                                    </div>

                                    <InputError message={errors.password} className="mt-1.5 text-xs font-semibold text-red-600" />
                                </div>

                                {/* Remember & Forgot Password */}
                                <div className="flex items-center justify-between">
                                    <label className="flex items-center cursor-pointer select-none">
                                        <Checkbox
                                            name="remember"
                                            checked={data.remember}
                                            onChange={(e) => setData('remember', e.target.checked)}
                                            className="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-950"
                                        />
                                        <span className="ms-2 text-sm font-semibold text-slate-600 dark:text-slate-300">
                                            Recordarme
                                        </span>
                                    </label>

                                    {canResetPassword && (
                                        <Link
                                            href={route('password.request')}
                                            className="text-xs font-bold text-indigo-600 hover:text-indigo-700 hover:underline dark:text-indigo-400 dark:hover:text-indigo-300"
                                        >
                                            ¿Olvidaste tu contraseña?
                                        </Link>
                                    )}
                                </div>

                                {/* Submit Button */}
                                <div>
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="w-full flex justify-center items-center gap-2 rounded-2xl bg-indigo-600 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition-all hover:bg-indigo-700 hover:shadow-indigo-600/30 hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50 disabled:pointer-events-none"
                                    >
                                        {processing ? 'Iniciando sesión...' : 'Ingresar al sistema'}
                                        <ArrowRight className="h-4 w-4" />
                                    </button>
                                </div>
                            </form>

                            {/* Divider & back link */}
                            <div className="mt-6 flex justify-center border-t border-slate-100 pt-6 dark:border-slate-800">
                                <Link
                                    href="/"
                                    className="inline-flex items-center gap-2 text-xs font-bold text-slate-500 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100"
                                >
                                    <ArrowLeft className="h-4 w-4" />
                                    Volver al sitio público
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
