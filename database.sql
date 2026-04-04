--
-- PostgreSQL database dump
--

\restrict ypZQQTMl9fi89sDhENuY5FnaDuHRAC4FbT2ZgWtK0wx3876lmfQ6JRoUivWiyuc

-- Dumped from database version 16.13
-- Dumped by pg_dump version 16.13

-- Started on 2026-04-04 18:16:12

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 855 (class 1247 OID 16431)
-- Name: estado_animo_enum; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.estado_animo_enum AS ENUM (
    'bien',
    'regular',
    'mal'
);


ALTER TYPE public.estado_animo_enum OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 222 (class 1259 OID 16465)
-- Name: estres_materias; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.estres_materias (
    id integer NOT NULL,
    registro_id integer NOT NULL,
    materia_id integer NOT NULL,
    nivel_preocupacion smallint NOT NULL,
    CONSTRAINT estres_materias_nivel_preocupacion_check CHECK (((nivel_preocupacion >= 1) AND (nivel_preocupacion <= 5)))
);


ALTER TABLE public.estres_materias OWNER TO postgres;

--
-- TOC entry 221 (class 1259 OID 16464)
-- Name: estres_materias_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.estres_materias_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.estres_materias_id_seq OWNER TO postgres;

--
-- TOC entry 4866 (class 0 OID 0)
-- Dependencies: 221
-- Name: estres_materias_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.estres_materias_id_seq OWNED BY public.estres_materias.id;


--
-- TOC entry 218 (class 1259 OID 16439)
-- Name: materias; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.materias (
    id integer NOT NULL,
    nombre character varying(100) NOT NULL,
    color_hex character varying(7) DEFAULT '#6c757d'::character varying
);


ALTER TABLE public.materias OWNER TO postgres;

--
-- TOC entry 217 (class 1259 OID 16438)
-- Name: materias_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.materias_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.materias_id_seq OWNER TO postgres;

--
-- TOC entry 4867 (class 0 OID 0)
-- Dependencies: 217
-- Name: materias_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.materias_id_seq OWNED BY public.materias.id;


--
-- TOC entry 224 (class 1259 OID 16483)
-- Name: recomendaciones; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.recomendaciones (
    id integer NOT NULL,
    categoria character varying(50) NOT NULL,
    titulo character varying(150) NOT NULL,
    descripcion text NOT NULL,
    nivel_min smallint NOT NULL,
    nivel_max smallint NOT NULL,
    CONSTRAINT recomendaciones_check CHECK ((nivel_min <= nivel_max))
);


ALTER TABLE public.recomendaciones OWNER TO postgres;

--
-- TOC entry 223 (class 1259 OID 16482)
-- Name: recomendaciones_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.recomendaciones_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.recomendaciones_id_seq OWNER TO postgres;

--
-- TOC entry 4868 (class 0 OID 0)
-- Dependencies: 223
-- Name: recomendaciones_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.recomendaciones_id_seq OWNED BY public.recomendaciones.id;


--
-- TOC entry 226 (class 1259 OID 16493)
-- Name: recomendaciones_usuario; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.recomendaciones_usuario (
    id integer NOT NULL,
    usuario_id integer NOT NULL,
    recomendacion_id integer NOT NULL,
    vista boolean DEFAULT false,
    fecha_enviada timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.recomendaciones_usuario OWNER TO postgres;

--
-- TOC entry 225 (class 1259 OID 16492)
-- Name: recomendaciones_usuario_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.recomendaciones_usuario_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.recomendaciones_usuario_id_seq OWNER TO postgres;

--
-- TOC entry 4869 (class 0 OID 0)
-- Dependencies: 225
-- Name: recomendaciones_usuario_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.recomendaciones_usuario_id_seq OWNED BY public.recomendaciones_usuario.id;


--
-- TOC entry 220 (class 1259 OID 16447)
-- Name: registros_estres; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.registros_estres (
    id integer NOT NULL,
    usuario_id integer NOT NULL,
    fecha date DEFAULT CURRENT_DATE NOT NULL,
    nivel_estres smallint NOT NULL,
    horas_sueno numeric(3,1),
    horas_estudio numeric(3,1),
    estado_animo public.estado_animo_enum DEFAULT 'regular'::public.estado_animo_enum,
    notas text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT registros_estres_nivel_estres_check CHECK (((nivel_estres >= 1) AND (nivel_estres <= 10)))
);


ALTER TABLE public.registros_estres OWNER TO postgres;

--
-- TOC entry 219 (class 1259 OID 16446)
-- Name: registros_estres_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.registros_estres_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.registros_estres_id_seq OWNER TO postgres;

--
-- TOC entry 4870 (class 0 OID 0)
-- Dependencies: 219
-- Name: registros_estres_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.registros_estres_id_seq OWNED BY public.registros_estres.id;


--
-- TOC entry 216 (class 1259 OID 16410)
-- Name: usuarios; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.usuarios (
    id integer NOT NULL,
    nombre character varying(100) NOT NULL,
    email character varying(150) NOT NULL,
    usuario character varying(80),
    contrasena text NOT NULL,
    created_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.usuarios OWNER TO postgres;

--
-- TOC entry 227 (class 1259 OID 16515)
-- Name: resumen_estres; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.resumen_estres AS
 SELECT u.id AS usuario_id,
    u.nombre,
    count(r.id) AS total_registros,
    round(avg(r.nivel_estres), 1) AS promedio_estres,
    max(r.nivel_estres) AS estres_maximo,
    round(avg(r.horas_sueno), 1) AS promedio_sueno,
    max(r.fecha) AS ultimo_registro
   FROM (public.usuarios u
     LEFT JOIN public.registros_estres r ON ((r.usuario_id = u.id)))
  GROUP BY u.id, u.nombre;


ALTER VIEW public.resumen_estres OWNER TO postgres;

--
-- TOC entry 215 (class 1259 OID 16409)
-- Name: usuarios_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.usuarios_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.usuarios_id_seq OWNER TO postgres;

--
-- TOC entry 4871 (class 0 OID 0)
-- Dependencies: 215
-- Name: usuarios_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.usuarios_id_seq OWNED BY public.usuarios.id;


--
-- TOC entry 4674 (class 2604 OID 16468)
-- Name: estres_materias id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.estres_materias ALTER COLUMN id SET DEFAULT nextval('public.estres_materias_id_seq'::regclass);


--
-- TOC entry 4668 (class 2604 OID 16442)
-- Name: materias id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.materias ALTER COLUMN id SET DEFAULT nextval('public.materias_id_seq'::regclass);


--
-- TOC entry 4675 (class 2604 OID 16486)
-- Name: recomendaciones id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.recomendaciones ALTER COLUMN id SET DEFAULT nextval('public.recomendaciones_id_seq'::regclass);


--
-- TOC entry 4676 (class 2604 OID 16496)
-- Name: recomendaciones_usuario id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.recomendaciones_usuario ALTER COLUMN id SET DEFAULT nextval('public.recomendaciones_usuario_id_seq'::regclass);


--
-- TOC entry 4670 (class 2604 OID 16450)
-- Name: registros_estres id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.registros_estres ALTER COLUMN id SET DEFAULT nextval('public.registros_estres_id_seq'::regclass);


--
-- TOC entry 4666 (class 2604 OID 16413)
-- Name: usuarios id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuarios ALTER COLUMN id SET DEFAULT nextval('public.usuarios_id_seq'::regclass);


--
-- TOC entry 4856 (class 0 OID 16465)
-- Dependencies: 222
-- Data for Name: estres_materias; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.estres_materias (id, registro_id, materia_id, nivel_preocupacion) FROM stdin;
\.


--
-- TOC entry 4852 (class 0 OID 16439)
-- Dependencies: 218
-- Data for Name: materias; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.materias (id, nombre, color_hex) FROM stdin;
1	Cálculo	#e74c3c
2	Física	#e67e22
3	Programación	#3498db
4	Sistemas Operativos	#9b59b6
5	Ética	#27ae60
\.


--
-- TOC entry 4858 (class 0 OID 16483)
-- Dependencies: 224
-- Data for Name: recomendaciones; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.recomendaciones (id, categoria, titulo, descripcion, nivel_min, nivel_max) FROM stdin;
1	sueño	Mejora tu descanso	Intenta dormir al menos 7 horas. Evita el celular 30 min antes de dormir.	1	4
2	descanso	Toma pausas activas	Cada 45 minutos de estudio, descansa 10 minutos. Estira el cuerpo.	3	6
3	estudio	Técnica Pomodoro	Divide tu estudio en bloques de 25 min con 5 min de descanso entre cada uno.	4	7
4	social	Habla con alguien	Comparte cómo te sientes con un compañero o familiar. No estudies en aislamiento.	6	8
5	sueño	Prioriza el sueño hoy	Con este nivel de estrés, dormir bien es más importante que estudiar de madrugada.	7	10
6	descanso	Busca apoyo universitario	Considera hablar con un orientador o consejero académico de tu facultad.	9	10
\.


--
-- TOC entry 4860 (class 0 OID 16493)
-- Dependencies: 226
-- Data for Name: recomendaciones_usuario; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.recomendaciones_usuario (id, usuario_id, recomendacion_id, vista, fecha_enviada) FROM stdin;
\.


--
-- TOC entry 4854 (class 0 OID 16447)
-- Dependencies: 220
-- Data for Name: registros_estres; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.registros_estres (id, usuario_id, fecha, nivel_estres, horas_sueno, horas_estudio, estado_animo, notas, created_at) FROM stdin;
\.


--
-- TOC entry 4850 (class 0 OID 16410)
-- Dependencies: 216
-- Data for Name: usuarios; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.usuarios (id, nombre, email, usuario, contrasena, created_at) FROM stdin;
1	Test Usuario	test@test.com	\N	$2y$10$NRAFLa.dotyllAxQfoqhmOhKkuQ7rE8AcZF1SZQb7V1iSNbAArbju	2026-04-04 19:44:24.795863
2	Haysel paulino	hayselpaulino@gmail.com	\N	$2y$10$gqHViPppfGVJA/DiSPBTIe3B4j/Dvwgpn3MgGxxg2Ol7.Hs00uHOu	2026-04-04 21:05:49.431039
\.


--
-- TOC entry 4872 (class 0 OID 0)
-- Dependencies: 221
-- Name: estres_materias_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.estres_materias_id_seq', 1, false);


--
-- TOC entry 4873 (class 0 OID 0)
-- Dependencies: 217
-- Name: materias_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.materias_id_seq', 5, true);


--
-- TOC entry 4874 (class 0 OID 0)
-- Dependencies: 223
-- Name: recomendaciones_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.recomendaciones_id_seq', 6, true);


--
-- TOC entry 4875 (class 0 OID 0)
-- Dependencies: 225
-- Name: recomendaciones_usuario_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.recomendaciones_usuario_id_seq', 1, false);


--
-- TOC entry 4876 (class 0 OID 0)
-- Dependencies: 219
-- Name: registros_estres_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.registros_estres_id_seq', 1, false);


--
-- TOC entry 4877 (class 0 OID 0)
-- Dependencies: 215
-- Name: usuarios_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.usuarios_id_seq', 2, true);


--
-- TOC entry 4693 (class 2606 OID 16471)
-- Name: estres_materias estres_materias_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.estres_materias
    ADD CONSTRAINT estres_materias_pkey PRIMARY KEY (id);


--
-- TOC entry 4687 (class 2606 OID 16445)
-- Name: materias materias_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.materias
    ADD CONSTRAINT materias_pkey PRIMARY KEY (id);


--
-- TOC entry 4695 (class 2606 OID 16491)
-- Name: recomendaciones recomendaciones_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.recomendaciones
    ADD CONSTRAINT recomendaciones_pkey PRIMARY KEY (id);


--
-- TOC entry 4699 (class 2606 OID 16500)
-- Name: recomendaciones_usuario recomendaciones_usuario_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.recomendaciones_usuario
    ADD CONSTRAINT recomendaciones_usuario_pkey PRIMARY KEY (id);


--
-- TOC entry 4691 (class 2606 OID 16458)
-- Name: registros_estres registros_estres_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.registros_estres
    ADD CONSTRAINT registros_estres_pkey PRIMARY KEY (id);


--
-- TOC entry 4683 (class 2606 OID 16420)
-- Name: usuarios usuarios_email_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_email_key UNIQUE (email);


--
-- TOC entry 4685 (class 2606 OID 16418)
-- Name: usuarios usuarios_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_pkey PRIMARY KEY (id);


--
-- TOC entry 4696 (class 1259 OID 16514)
-- Name: idx_rec_fecha; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_rec_fecha ON public.recomendaciones_usuario USING btree (fecha_enviada);


--
-- TOC entry 4697 (class 1259 OID 16513)
-- Name: idx_rec_usuario; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_rec_usuario ON public.recomendaciones_usuario USING btree (usuario_id);


--
-- TOC entry 4688 (class 1259 OID 16512)
-- Name: idx_registros_fecha; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_registros_fecha ON public.registros_estres USING btree (fecha);


--
-- TOC entry 4689 (class 1259 OID 16511)
-- Name: idx_registros_usuario; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_registros_usuario ON public.registros_estres USING btree (usuario_id);


--
-- TOC entry 4701 (class 2606 OID 16477)
-- Name: estres_materias estres_materias_materia_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.estres_materias
    ADD CONSTRAINT estres_materias_materia_id_fkey FOREIGN KEY (materia_id) REFERENCES public.materias(id);


--
-- TOC entry 4702 (class 2606 OID 16472)
-- Name: estres_materias estres_materias_registro_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.estres_materias
    ADD CONSTRAINT estres_materias_registro_id_fkey FOREIGN KEY (registro_id) REFERENCES public.registros_estres(id) ON DELETE CASCADE;


--
-- TOC entry 4703 (class 2606 OID 16506)
-- Name: recomendaciones_usuario recomendaciones_usuario_recomendacion_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.recomendaciones_usuario
    ADD CONSTRAINT recomendaciones_usuario_recomendacion_id_fkey FOREIGN KEY (recomendacion_id) REFERENCES public.recomendaciones(id);


--
-- TOC entry 4704 (class 2606 OID 16501)
-- Name: recomendaciones_usuario recomendaciones_usuario_usuario_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.recomendaciones_usuario
    ADD CONSTRAINT recomendaciones_usuario_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE CASCADE;


--
-- TOC entry 4700 (class 2606 OID 16459)
-- Name: registros_estres registros_estres_usuario_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.registros_estres
    ADD CONSTRAINT registros_estres_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE CASCADE;


-- Completed on 2026-04-04 18:16:12

--
-- PostgreSQL database dump complete
--

\unrestrict ypZQQTMl9fi89sDhENuY5FnaDuHRAC4FbT2ZgWtK0wx3876lmfQ6JRoUivWiyuc

