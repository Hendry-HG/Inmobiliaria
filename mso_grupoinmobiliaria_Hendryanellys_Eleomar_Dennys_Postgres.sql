--
-- PostgreSQL database dump
--

\restrict nsJfhcC09IgLgDaHXVWGAdhFx07p79aoMfIC2BOUKP4TK3fQX2uNHHgnv2rOkgS

-- Dumped from database version 18.3
-- Dumped by pg_dump version 18.3

-- Started on 2026-07-17 21:29:40

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 4 (class 2615 OID 2200)
-- Name: public; Type: SCHEMA; Schema: -; Owner: pg_database_owner
--

CREATE SCHEMA public;


ALTER SCHEMA public OWNER TO pg_database_owner;

--
-- TOC entry 5473 (class 0 OID 0)
-- Dependencies: 4
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: pg_database_owner
--

COMMENT ON SCHEMA public IS 'standard public schema';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 274 (class 1259 OID 140760)
-- Name: account_reactivation_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.account_reactivation_tokens (
    id bigint NOT NULL,
    email character varying(255) NOT NULL,
    token character varying(64) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.account_reactivation_tokens OWNER TO postgres;

--
-- TOC entry 273 (class 1259 OID 140759)
-- Name: account_reactivation_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.account_reactivation_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.account_reactivation_tokens_id_seq OWNER TO postgres;

--
-- TOC entry 5474 (class 0 OID 0)
-- Dependencies: 273
-- Name: account_reactivation_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.account_reactivation_tokens_id_seq OWNED BY public.account_reactivation_tokens.id;


--
-- TOC entry 280 (class 1259 OID 140826)
-- Name: appointment_settings; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.appointment_settings (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    daily_config json,
    valid_from date,
    valid_to date,
    apply_always boolean DEFAULT true NOT NULL,
    slot_duration integer DEFAULT 60 NOT NULL,
    break_duration integer DEFAULT 15 NOT NULL,
    notify_client boolean DEFAULT true NOT NULL,
    reminder_minutes integer DEFAULT 60 NOT NULL,
    exceptions json,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.appointment_settings OWNER TO postgres;

--
-- TOC entry 279 (class 1259 OID 140825)
-- Name: appointment_settings_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.appointment_settings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.appointment_settings_id_seq OWNER TO postgres;

--
-- TOC entry 5475 (class 0 OID 0)
-- Dependencies: 279
-- Name: appointment_settings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.appointment_settings_id_seq OWNED BY public.appointment_settings.id;


--
-- TOC entry 249 (class 1259 OID 140426)
-- Name: appointments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.appointments (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    property_id bigint NOT NULL,
    asesor_id bigint NOT NULL,
    scheduled_date timestamp(0) without time zone NOT NULL,
    end_date timestamp(0) without time zone,
    status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    contact_name character varying(255),
    contact_phone character varying(255),
    contact_email character varying(255),
    message text,
    notes text,
    result_notes text,
    client_attended boolean DEFAULT false NOT NULL,
    property_sold boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT appointments_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'confirmed'::character varying, 'cancelled'::character varying, 'completed'::character varying, 'reprogrammed'::character varying, 'no_show'::character varying])::text[])))
);


ALTER TABLE public.appointments OWNER TO postgres;

--
-- TOC entry 248 (class 1259 OID 140425)
-- Name: appointments_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.appointments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.appointments_id_seq OWNER TO postgres;

--
-- TOC entry 5476 (class 0 OID 0)
-- Dependencies: 248
-- Name: appointments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.appointments_id_seq OWNED BY public.appointments.id;


--
-- TOC entry 261 (class 1259 OID 140631)
-- Name: audit_logs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.audit_logs (
    id bigint NOT NULL,
    user_id bigint,
    subject_type character varying(255),
    subject_id bigint,
    old_values json,
    new_values json,
    description character varying(255),
    ip_address character varying(45),
    user_agent text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    action character varying(255),
    event character varying(255),
    url text,
    user_type character varying(255),
    tags character varying(255),
    auditable_type character varying(255),
    auditable_id bigint
);


ALTER TABLE public.audit_logs OWNER TO postgres;

--
-- TOC entry 260 (class 1259 OID 140630)
-- Name: audit_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.audit_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.audit_logs_id_seq OWNER TO postgres;

--
-- TOC entry 5477 (class 0 OID 0)
-- Dependencies: 260
-- Name: audit_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.audit_logs_id_seq OWNED BY public.audit_logs.id;


--
-- TOC entry 225 (class 1259 OID 140154)
-- Name: cache; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO postgres;

--
-- TOC entry 226 (class 1259 OID 140165)
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO postgres;

--
-- TOC entry 243 (class 1259 OID 140295)
-- Name: categories; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.categories (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    slug character varying(255) NOT NULL,
    description text,
    icon character varying(255),
    is_active boolean DEFAULT true NOT NULL,
    "order" integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.categories OWNER TO postgres;

--
-- TOC entry 242 (class 1259 OID 140294)
-- Name: categories_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.categories_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.categories_id_seq OWNER TO postgres;

--
-- TOC entry 5478 (class 0 OID 0)
-- Dependencies: 242
-- Name: categories_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.categories_id_seq OWNED BY public.categories.id;


--
-- TOC entry 241 (class 1259 OID 140280)
-- Name: cities; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cities (
    id bigint NOT NULL,
    parish_id bigint NOT NULL,
    name character varying(100) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.cities OWNER TO postgres;

--
-- TOC entry 240 (class 1259 OID 140279)
-- Name: cities_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.cities_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.cities_id_seq OWNER TO postgres;

--
-- TOC entry 5479 (class 0 OID 0)
-- Dependencies: 240
-- Name: cities_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.cities_id_seq OWNED BY public.cities.id;


--
-- TOC entry 255 (class 1259 OID 140549)
-- Name: conversations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.conversations (
    id bigint NOT NULL,
    client_id bigint NOT NULL,
    asesor_id bigint NOT NULL,
    subject character varying(255),
    last_message_at timestamp(0) without time zone,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.conversations OWNER TO postgres;

--
-- TOC entry 254 (class 1259 OID 140548)
-- Name: conversations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.conversations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.conversations_id_seq OWNER TO postgres;

--
-- TOC entry 5480 (class 0 OID 0)
-- Dependencies: 254
-- Name: conversations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.conversations_id_seq OWNED BY public.conversations.id;


--
-- TOC entry 233 (class 1259 OID 140226)
-- Name: countries; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.countries (
    id bigint NOT NULL,
    name character varying(100) NOT NULL,
    code character varying(3),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    phone_code character varying(5),
    phone_format character varying(50),
    phone_min_length integer DEFAULT 7 NOT NULL,
    phone_max_length integer DEFAULT 15 NOT NULL
);


ALTER TABLE public.countries OWNER TO postgres;

--
-- TOC entry 232 (class 1259 OID 140225)
-- Name: countries_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.countries_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.countries_id_seq OWNER TO postgres;

--
-- TOC entry 5481 (class 0 OID 0)
-- Dependencies: 232
-- Name: countries_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.countries_id_seq OWNED BY public.countries.id;


--
-- TOC entry 231 (class 1259 OID 140207)
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO postgres;

--
-- TOC entry 230 (class 1259 OID 140206)
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.failed_jobs_id_seq OWNER TO postgres;

--
-- TOC entry 5482 (class 0 OID 0)
-- Dependencies: 230
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- TOC entry 251 (class 1259 OID 140473)
-- Name: favorites; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.favorites (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    property_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.favorites OWNER TO postgres;

--
-- TOC entry 250 (class 1259 OID 140472)
-- Name: favorites_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.favorites_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.favorites_id_seq OWNER TO postgres;

--
-- TOC entry 5483 (class 0 OID 0)
-- Dependencies: 250
-- Name: favorites_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.favorites_id_seq OWNED BY public.favorites.id;


--
-- TOC entry 229 (class 1259 OID 140192)
-- Name: job_batches; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


ALTER TABLE public.job_batches OWNER TO postgres;

--
-- TOC entry 228 (class 1259 OID 140177)
-- Name: jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


ALTER TABLE public.jobs OWNER TO postgres;

--
-- TOC entry 227 (class 1259 OID 140176)
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jobs_id_seq OWNER TO postgres;

--
-- TOC entry 5484 (class 0 OID 0)
-- Dependencies: 227
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- TOC entry 253 (class 1259 OID 140499)
-- Name: leads; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.leads (
    id bigint NOT NULL,
    user_id bigint,
    property_id bigint,
    asesor_id bigint,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    phone character varying(255) NOT NULL,
    id_type character varying(255),
    id_number character varying(255),
    source character varying(255) DEFAULT 'website'::character varying NOT NULL,
    source_detail character varying(255),
    interest_type character varying(255) DEFAULT 'compra'::character varying NOT NULL,
    budget_min numeric(12,2),
    budget_max numeric(12,2),
    preferences json,
    status character varying(255) DEFAULT 'nuevo'::character varying NOT NULL,
    notes text,
    last_contact timestamp(0) without time zone,
    contact_count integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT leads_interest_type_check CHECK (((interest_type)::text = ANY ((ARRAY['compra'::character varying, 'alquiler'::character varying, 'venta'::character varying, 'asesoria'::character varying])::text[]))),
    CONSTRAINT leads_status_check CHECK (((status)::text = ANY ((ARRAY['nuevo'::character varying, 'contactado'::character varying, 'calificado'::character varying, 'negociacion'::character varying, 'cerrado_ganado'::character varying, 'cerrado_perdido'::character varying, 'inactivo'::character varying])::text[])))
);


ALTER TABLE public.leads OWNER TO postgres;

--
-- TOC entry 252 (class 1259 OID 140498)
-- Name: leads_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.leads_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.leads_id_seq OWNER TO postgres;

--
-- TOC entry 5485 (class 0 OID 0)
-- Dependencies: 252
-- Name: leads_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.leads_id_seq OWNED BY public.leads.id;


--
-- TOC entry 257 (class 1259 OID 140580)
-- Name: messages; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.messages (
    id bigint NOT NULL,
    conversation_id bigint NOT NULL,
    user_id bigint NOT NULL,
    content text NOT NULL,
    is_read boolean DEFAULT false NOT NULL,
    read_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.messages OWNER TO postgres;

--
-- TOC entry 256 (class 1259 OID 140579)
-- Name: messages_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.messages_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.messages_id_seq OWNER TO postgres;

--
-- TOC entry 5486 (class 0 OID 0)
-- Dependencies: 256
-- Name: messages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.messages_id_seq OWNED BY public.messages.id;


--
-- TOC entry 220 (class 1259 OID 140088)
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- TOC entry 219 (class 1259 OID 140087)
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO postgres;

--
-- TOC entry 5487 (class 0 OID 0)
-- Dependencies: 219
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- TOC entry 270 (class 1259 OID 140714)
-- Name: model_has_permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.model_has_permissions (
    permission_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL
);


ALTER TABLE public.model_has_permissions OWNER TO postgres;

--
-- TOC entry 271 (class 1259 OID 140728)
-- Name: model_has_roles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.model_has_roles (
    role_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL
);


ALTER TABLE public.model_has_roles OWNER TO postgres;

--
-- TOC entry 237 (class 1259 OID 140250)
-- Name: municipalities; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.municipalities (
    id bigint NOT NULL,
    state_id bigint NOT NULL,
    name character varying(100) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.municipalities OWNER TO postgres;

--
-- TOC entry 236 (class 1259 OID 140249)
-- Name: municipalities_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.municipalities_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.municipalities_id_seq OWNER TO postgres;

--
-- TOC entry 5488 (class 0 OID 0)
-- Dependencies: 236
-- Name: municipalities_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.municipalities_id_seq OWNED BY public.municipalities.id;


--
-- TOC entry 239 (class 1259 OID 140265)
-- Name: parishes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.parishes (
    id bigint NOT NULL,
    municipality_id bigint NOT NULL,
    name character varying(100) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.parishes OWNER TO postgres;

--
-- TOC entry 238 (class 1259 OID 140264)
-- Name: parishes_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.parishes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.parishes_id_seq OWNER TO postgres;

--
-- TOC entry 5489 (class 0 OID 0)
-- Dependencies: 238
-- Name: parishes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.parishes_id_seq OWNED BY public.parishes.id;


--
-- TOC entry 223 (class 1259 OID 140133)
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO postgres;

--
-- TOC entry 267 (class 1259 OID 140687)
-- Name: permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.permissions (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.permissions OWNER TO postgres;

--
-- TOC entry 266 (class 1259 OID 140686)
-- Name: permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.permissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.permissions_id_seq OWNER TO postgres;

--
-- TOC entry 5490 (class 0 OID 0)
-- Dependencies: 266
-- Name: permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.permissions_id_seq OWNED BY public.permissions.id;


--
-- TOC entry 265 (class 1259 OID 140669)
-- Name: personal_access_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.personal_access_tokens (
    id bigint NOT NULL,
    tokenable_type character varying(255) NOT NULL,
    tokenable_id bigint NOT NULL,
    name text NOT NULL,
    token character varying(64) NOT NULL,
    abilities text,
    last_used_at timestamp(0) without time zone,
    expires_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.personal_access_tokens OWNER TO postgres;

--
-- TOC entry 264 (class 1259 OID 140668)
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.personal_access_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.personal_access_tokens_id_seq OWNER TO postgres;

--
-- TOC entry 5491 (class 0 OID 0)
-- Dependencies: 264
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.personal_access_tokens_id_seq OWNED BY public.personal_access_tokens.id;


--
-- TOC entry 245 (class 1259 OID 140313)
-- Name: properties; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.properties (
    id bigint NOT NULL,
    title character varying(255) NOT NULL,
    description text NOT NULL,
    price numeric(14,2) NOT NULL,
    price_currency character varying(255) DEFAULT 'USD'::character varying NOT NULL,
    country_id bigint,
    state_id bigint,
    municipality_id bigint,
    parish_id bigint,
    city_id bigint,
    address character varying(255),
    location character varying(255),
    sector character varying(255),
    city character varying(255),
    state character varying(255),
    country character varying(255),
    zip_code character varying(255),
    bedrooms integer,
    bathrooms integer,
    parking_spaces integer,
    area numeric(10,2),
    land_area numeric(10,2),
    floors integer,
    year_built integer,
    type character varying(255) DEFAULT 'venta'::character varying NOT NULL,
    status character varying(255) DEFAULT 'borrador'::character varying NOT NULL,
    features json,
    user_id bigint NOT NULL,
    category_id bigint,
    views integer DEFAULT 0 NOT NULL,
    inquiries integer DEFAULT 0 NOT NULL,
    is_featured boolean DEFAULT false NOT NULL,
    featured_until date,
    meta_data json,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    CONSTRAINT properties_status_check CHECK (((status)::text = ANY ((ARRAY['borrador'::character varying, 'pendiente'::character varying, 'publicada'::character varying, 'vendida'::character varying, 'alquilada'::character varying, 'inactiva'::character varying])::text[]))),
    CONSTRAINT properties_type_check CHECK (((type)::text = ANY ((ARRAY['venta'::character varying, 'alquiler'::character varying, 'venta/alquiler'::character varying])::text[])))
);


ALTER TABLE public.properties OWNER TO postgres;

--
-- TOC entry 244 (class 1259 OID 140312)
-- Name: properties_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.properties_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.properties_id_seq OWNER TO postgres;

--
-- TOC entry 5492 (class 0 OID 0)
-- Dependencies: 244
-- Name: properties_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.properties_id_seq OWNED BY public.properties.id;


--
-- TOC entry 247 (class 1259 OID 140401)
-- Name: property_images; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.property_images (
    id bigint NOT NULL,
    property_id bigint NOT NULL,
    image_path character varying(255) NOT NULL,
    thumbnail_path character varying(255),
    caption character varying(255),
    "order" integer DEFAULT 0 NOT NULL,
    is_primary boolean DEFAULT false NOT NULL,
    mime_type character varying(255),
    size integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.property_images OWNER TO postgres;

--
-- TOC entry 246 (class 1259 OID 140400)
-- Name: property_images_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.property_images_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.property_images_id_seq OWNER TO postgres;

--
-- TOC entry 5493 (class 0 OID 0)
-- Dependencies: 246
-- Name: property_images_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.property_images_id_seq OWNED BY public.property_images.id;


--
-- TOC entry 276 (class 1259 OID 140777)
-- Name: report_snapshots; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.report_snapshots (
    id bigint NOT NULL,
    user_id bigint,
    report_type character varying(255) NOT NULL,
    period character varying(255) NOT NULL,
    data json NOT NULL,
    file_path character varying(255),
    status character varying(255) DEFAULT 'generated'::character varying NOT NULL,
    sent_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT report_snapshots_status_check CHECK (((status)::text = ANY ((ARRAY['generated'::character varying, 'sent'::character varying, 'failed'::character varying])::text[])))
);


ALTER TABLE public.report_snapshots OWNER TO postgres;

--
-- TOC entry 275 (class 1259 OID 140776)
-- Name: report_snapshots_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.report_snapshots_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.report_snapshots_id_seq OWNER TO postgres;

--
-- TOC entry 5494 (class 0 OID 0)
-- Dependencies: 275
-- Name: report_snapshots_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.report_snapshots_id_seq OWNED BY public.report_snapshots.id;


--
-- TOC entry 272 (class 1259 OID 140742)
-- Name: role_has_permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.role_has_permissions (
    permission_id bigint NOT NULL,
    role_id bigint NOT NULL
);


ALTER TABLE public.role_has_permissions OWNER TO postgres;

--
-- TOC entry 269 (class 1259 OID 140701)
-- Name: roles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.roles (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.roles OWNER TO postgres;

--
-- TOC entry 268 (class 1259 OID 140700)
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.roles_id_seq OWNER TO postgres;

--
-- TOC entry 5495 (class 0 OID 0)
-- Dependencies: 268
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


--
-- TOC entry 284 (class 1259 OID 140886)
-- Name: service_galleries; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.service_galleries (
    id bigint NOT NULL,
    service_id bigint NOT NULL,
    image_path character varying(255) NOT NULL,
    title character varying(255),
    alt_text character varying(255),
    "order" integer DEFAULT 0 NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.service_galleries OWNER TO postgres;

--
-- TOC entry 283 (class 1259 OID 140885)
-- Name: service_galleries_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.service_galleries_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.service_galleries_id_seq OWNER TO postgres;

--
-- TOC entry 5496 (class 0 OID 0)
-- Dependencies: 283
-- Name: service_galleries_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.service_galleries_id_seq OWNED BY public.service_galleries.id;


--
-- TOC entry 282 (class 1259 OID 140864)
-- Name: services; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.services (
    id bigint NOT NULL,
    title character varying(255) NOT NULL,
    slug character varying(255) NOT NULL,
    description text,
    icon text,
    color character varying(255) DEFAULT '#c5a059'::character varying NOT NULL,
    badge character varying(255),
    image character varying(255),
    features json,
    external_url character varying(255),
    "order" integer DEFAULT 0 NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    is_featured boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.services OWNER TO postgres;

--
-- TOC entry 281 (class 1259 OID 140863)
-- Name: services_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.services_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.services_id_seq OWNER TO postgres;

--
-- TOC entry 5497 (class 0 OID 0)
-- Dependencies: 281
-- Name: services_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.services_id_seq OWNED BY public.services.id;


--
-- TOC entry 224 (class 1259 OID 140142)
-- Name: sessions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO postgres;

--
-- TOC entry 259 (class 1259 OID 140612)
-- Name: settings; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.settings (
    id bigint NOT NULL,
    key character varying(255) NOT NULL,
    value text,
    type character varying(255) DEFAULT 'text'::character varying NOT NULL,
    "group" character varying(255) DEFAULT 'general'::character varying NOT NULL,
    label character varying(255),
    description text,
    "order" integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.settings OWNER TO postgres;

--
-- TOC entry 258 (class 1259 OID 140611)
-- Name: settings_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.settings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.settings_id_seq OWNER TO postgres;

--
-- TOC entry 5498 (class 0 OID 0)
-- Dependencies: 258
-- Name: settings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.settings_id_seq OWNED BY public.settings.id;


--
-- TOC entry 278 (class 1259 OID 140804)
-- Name: site_configurations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.site_configurations (
    id bigint NOT NULL,
    hero_badge character varying(255) DEFAULT 'Exclusividad & Confort'::character varying NOT NULL,
    hero_title_line1 character varying(255) DEFAULT 'El Arte de'::character varying NOT NULL,
    hero_title_line2 character varying(255) DEFAULT 'Vivir Bien'::character varying NOT NULL,
    hero_subtitle text DEFAULT 'Descubre una curaduría exclusiva de propiedades de lujo en las mejores zonas de Venezuela.'::text NOT NULL,
    hero_images json,
    hero_image_paths character varying(255),
    featured_badge character varying(255) DEFAULT 'Colección Exclusiva'::character varying NOT NULL,
    featured_title character varying(255) DEFAULT 'Propiedades Destacadas'::character varying NOT NULL,
    featured_properties json,
    support_whatsapp character varying(255),
    support_instagram character varying(255),
    support_phone character varying(255),
    support_email character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.site_configurations OWNER TO postgres;

--
-- TOC entry 277 (class 1259 OID 140803)
-- Name: site_configurations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.site_configurations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.site_configurations_id_seq OWNER TO postgres;

--
-- TOC entry 5499 (class 0 OID 0)
-- Dependencies: 277
-- Name: site_configurations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.site_configurations_id_seq OWNED BY public.site_configurations.id;


--
-- TOC entry 235 (class 1259 OID 140235)
-- Name: states; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.states (
    id bigint NOT NULL,
    country_id bigint NOT NULL,
    name character varying(100) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.states OWNER TO postgres;

--
-- TOC entry 234 (class 1259 OID 140234)
-- Name: states_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.states_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.states_id_seq OWNER TO postgres;

--
-- TOC entry 5500 (class 0 OID 0)
-- Dependencies: 234
-- Name: states_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.states_id_seq OWNED BY public.states.id;


--
-- TOC entry 263 (class 1259 OID 140648)
-- Name: user_notifications; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.user_notifications (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    title character varying(255) NOT NULL,
    message text NOT NULL,
    type character varying(255) DEFAULT 'info'::character varying NOT NULL,
    url character varying(255),
    data json,
    read_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.user_notifications OWNER TO postgres;

--
-- TOC entry 262 (class 1259 OID 140647)
-- Name: user_notifications_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.user_notifications_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.user_notifications_id_seq OWNER TO postgres;

--
-- TOC entry 5501 (class 0 OID 0)
-- Dependencies: 262
-- Name: user_notifications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.user_notifications_id_seq OWNED BY public.user_notifications.id;


--
-- TOC entry 222 (class 1259 OID 140098)
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    last_name character varying(255),
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    phone character varying(255),
    profile_photo character varying(255),
    bio text,
    specialization character varying(255),
    social_links json,
    id_type character varying(255),
    id_number character varying(255),
    country_id bigint,
    state_id bigint,
    municipality_id bigint,
    parish_id bigint,
    city_id bigint,
    address text,
    security_questions json,
    security_answer_1 character varying(255),
    security_answer_2 character varying(255),
    security_answer_3 character varying(255),
    security_questions_set_at timestamp(0) without time zone,
    is_active boolean DEFAULT true NOT NULL,
    is_online boolean DEFAULT false NOT NULL,
    last_seen_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.users OWNER TO postgres;

--
-- TOC entry 5502 (class 0 OID 0)
-- Dependencies: 222
-- Name: COLUMN users.id_type; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.users.id_type IS 'V, E, J, P';


--
-- TOC entry 221 (class 1259 OID 140097)
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO postgres;

--
-- TOC entry 5503 (class 0 OID 0)
-- Dependencies: 221
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- TOC entry 4978 (class 2604 OID 140763)
-- Name: account_reactivation_tokens id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.account_reactivation_tokens ALTER COLUMN id SET DEFAULT nextval('public.account_reactivation_tokens_id_seq'::regclass);


--
-- TOC entry 4988 (class 2604 OID 140829)
-- Name: appointment_settings id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.appointment_settings ALTER COLUMN id SET DEFAULT nextval('public.appointment_settings_id_seq'::regclass);


--
-- TOC entry 4954 (class 2604 OID 140429)
-- Name: appointments id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.appointments ALTER COLUMN id SET DEFAULT nextval('public.appointments_id_seq'::regclass);


--
-- TOC entry 4972 (class 2604 OID 140634)
-- Name: audit_logs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.audit_logs ALTER COLUMN id SET DEFAULT nextval('public.audit_logs_id_seq'::regclass);


--
-- TOC entry 4941 (class 2604 OID 140298)
-- Name: categories id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categories ALTER COLUMN id SET DEFAULT nextval('public.categories_id_seq'::regclass);


--
-- TOC entry 4940 (class 2604 OID 140283)
-- Name: cities id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cities ALTER COLUMN id SET DEFAULT nextval('public.cities_id_seq'::regclass);


--
-- TOC entry 4964 (class 2604 OID 140552)
-- Name: conversations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations ALTER COLUMN id SET DEFAULT nextval('public.conversations_id_seq'::regclass);


--
-- TOC entry 4934 (class 2604 OID 140229)
-- Name: countries id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.countries ALTER COLUMN id SET DEFAULT nextval('public.countries_id_seq'::regclass);


--
-- TOC entry 4932 (class 2604 OID 140210)
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- TOC entry 4958 (class 2604 OID 140476)
-- Name: favorites id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.favorites ALTER COLUMN id SET DEFAULT nextval('public.favorites_id_seq'::regclass);


--
-- TOC entry 4931 (class 2604 OID 140180)
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- TOC entry 4959 (class 2604 OID 140502)
-- Name: leads id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leads ALTER COLUMN id SET DEFAULT nextval('public.leads_id_seq'::regclass);


--
-- TOC entry 4966 (class 2604 OID 140583)
-- Name: messages id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.messages ALTER COLUMN id SET DEFAULT nextval('public.messages_id_seq'::regclass);


--
-- TOC entry 4927 (class 2604 OID 140091)
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- TOC entry 4938 (class 2604 OID 140253)
-- Name: municipalities id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.municipalities ALTER COLUMN id SET DEFAULT nextval('public.municipalities_id_seq'::regclass);


--
-- TOC entry 4939 (class 2604 OID 140268)
-- Name: parishes id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.parishes ALTER COLUMN id SET DEFAULT nextval('public.parishes_id_seq'::regclass);


--
-- TOC entry 4976 (class 2604 OID 140690)
-- Name: permissions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions ALTER COLUMN id SET DEFAULT nextval('public.permissions_id_seq'::regclass);


--
-- TOC entry 4975 (class 2604 OID 140672)
-- Name: personal_access_tokens id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens ALTER COLUMN id SET DEFAULT nextval('public.personal_access_tokens_id_seq'::regclass);


--
-- TOC entry 4944 (class 2604 OID 140316)
-- Name: properties id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.properties ALTER COLUMN id SET DEFAULT nextval('public.properties_id_seq'::regclass);


--
-- TOC entry 4951 (class 2604 OID 140404)
-- Name: property_images id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.property_images ALTER COLUMN id SET DEFAULT nextval('public.property_images_id_seq'::regclass);


--
-- TOC entry 4979 (class 2604 OID 140780)
-- Name: report_snapshots id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.report_snapshots ALTER COLUMN id SET DEFAULT nextval('public.report_snapshots_id_seq'::regclass);


--
-- TOC entry 4977 (class 2604 OID 140704)
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- TOC entry 5000 (class 2604 OID 140889)
-- Name: service_galleries id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.service_galleries ALTER COLUMN id SET DEFAULT nextval('public.service_galleries_id_seq'::regclass);


--
-- TOC entry 4995 (class 2604 OID 140867)
-- Name: services id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.services ALTER COLUMN id SET DEFAULT nextval('public.services_id_seq'::regclass);


--
-- TOC entry 4968 (class 2604 OID 140615)
-- Name: settings id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.settings ALTER COLUMN id SET DEFAULT nextval('public.settings_id_seq'::regclass);


--
-- TOC entry 4981 (class 2604 OID 140807)
-- Name: site_configurations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.site_configurations ALTER COLUMN id SET DEFAULT nextval('public.site_configurations_id_seq'::regclass);


--
-- TOC entry 4937 (class 2604 OID 140238)
-- Name: states id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.states ALTER COLUMN id SET DEFAULT nextval('public.states_id_seq'::regclass);


--
-- TOC entry 4973 (class 2604 OID 140651)
-- Name: user_notifications id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_notifications ALTER COLUMN id SET DEFAULT nextval('public.user_notifications_id_seq'::regclass);


--
-- TOC entry 4928 (class 2604 OID 140101)
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- TOC entry 5457 (class 0 OID 140760)
-- Dependencies: 274
-- Data for Name: account_reactivation_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.account_reactivation_tokens (id, email, token, created_at) FROM stdin;
\.


--
-- TOC entry 5463 (class 0 OID 140826)
-- Dependencies: 280
-- Data for Name: appointment_settings; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.appointment_settings (id, user_id, is_active, daily_config, valid_from, valid_to, apply_always, slot_duration, break_duration, notify_client, reminder_minutes, exceptions, created_at, updated_at) FROM stdin;
1	1	t	{"monday":{"max":5,"hours":["09:00","10:00","11:00","14:00","15:00","16:00"]},"tuesday":{"max":5,"hours":["09:00","10:00","11:00","14:00","15:00","16:00"]},"wednesday":{"max":5,"hours":["09:00","10:00","11:00","14:00","15:00","16:00"]},"thursday":{"max":5,"hours":["09:00","10:00","11:00","14:00","15:00","16:00"]},"friday":{"max":4,"hours":["09:00","10:00","11:00","14:00","15:00"]},"saturday":{"max":0,"hours":[]},"sunday":{"max":0,"hours":[]}}	\N	\N	t	60	15	t	60	[]	2026-07-18 00:21:46	2026-07-18 00:21:46
\.


--
-- TOC entry 5432 (class 0 OID 140426)
-- Dependencies: 249
-- Data for Name: appointments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.appointments (id, user_id, property_id, asesor_id, scheduled_date, end_date, status, contact_name, contact_phone, contact_email, message, notes, result_notes, client_attended, property_sold, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5444 (class 0 OID 140631)
-- Dependencies: 261
-- Data for Name: audit_logs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.audit_logs (id, user_id, subject_type, subject_id, old_values, new_values, description, ip_address, user_agent, created_at, updated_at, action, event, url, user_type, tags, auditable_type, auditable_id) FROM stdin;
1	1	App\\Models\\User	1	\N	\N	Inicio de sesión de Carlos	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36	2026-07-18 00:17:43	2026-07-18 00:17:43	login	login	http://127.0.0.1:8000/login	\N	\N	\N	\N
2	1	App\\Models\\Property	1	\N	{"title":"Casa de Lujo en Altamira con Piscina y Vista Panor\\u00e1mica","description":"Espectacular casa de lujo ubicada en la exclusiva zona de Altamira, una de las zonas m\\u00e1s prestigiosas de Caracas. Esta propiedad cuenta con 5 habitaciones, cada una con ba\\u00f1o privado y walk-in closet. La casa est\\u00e1 distribuida en 3 plantas con un dise\\u00f1o arquitect\\u00f3nico moderno y elegante.\\r\\n\\r\\nLa planta principal cuenta con un amplio sal\\u00f3n de doble altura, comedor formal, cocina totalmente equipada con electrodom\\u00e9sticos de \\u00faltima generaci\\u00f3n, sala de TV y un hermoso jard\\u00edn interior. La terraza posterior tiene una piscina climatizada, \\u00e1rea de barbacoa y un jard\\u00edn paisaj\\u00edstico con fuentes de agua.\\r\\n\\r\\nLa planta superior alberga las 5 habitaciones, incluyendo la suite principal con terraza privada y vista panor\\u00e1mica de la ciudad. La planta inferior tiene un \\u00e1rea de servicio con 2 habitaciones para el personal, lavander\\u00eda y un amplio garaje para 4 veh\\u00edculos.\\r\\n\\r\\nLa propiedad cuenta con sistema de seguridad 24\\/7, circuito cerrado de c\\u00e1maras, generador el\\u00e9ctrico, cisterna de agua y sistema de riego autom\\u00e1tico. Ideal para familias que buscan exclusividad, comodidad y seguridad en un entorno privilegiado.","price":"850000.00","price_currency":"USD","type":"venta","status":"publicada","category_id":"1","country_id":"1","state_id":"1","municipality_id":"1","parish_id":"1","city_id":"1","address":"Av. Principal de Altamira, Quinta Los Manantiales","bedrooms":"5","bathrooms":"5","parking_spaces":"4","area":"350","land_area":"500","floors":"3","year_built":"2018","features":["aire_acondicionado","balcon","camaras_seguridad","chimenea","cisterna","jardin","piscina","porteria","terraza","zonas_verdes"],"featured_until":null,"user_id":1,"location":"Venezuela, Distrito Capital, Libertador, Altagracia, Caracas, Av. Principal de Altamira, Quinta Los Manantiales","updated_at":"2026-07-18T00:20:39.000000Z","created_at":"2026-07-18T00:20:39.000000Z","id":1}	Carlos Rodríguez CREÓ la propiedad "Casa de Lujo en Altamira con Piscina y Vista Panorámica"	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36	2026-07-18 00:20:42	2026-07-18 00:20:42	created	created	http://127.0.0.1:8000/admin/properties	\N	\N	\N	\N
3	1	App\\Models\\Property	1	{"id":1,"title":"Casa de Lujo en Altamira con Piscina y Vista Panor\\u00e1mica","description":"Espectacular casa de lujo ubicada en la exclusiva zona de Altamira, una de las zonas m\\u00e1s prestigiosas de Caracas. Esta propiedad cuenta con 5 habitaciones, cada una con ba\\u00f1o privado y walk-in closet. La casa est\\u00e1 distribuida en 3 plantas con un dise\\u00f1o arquitect\\u00f3nico moderno y elegante.\\r\\n\\r\\nLa planta principal cuenta con un amplio sal\\u00f3n de doble altura, comedor formal, cocina totalmente equipada con electrodom\\u00e9sticos de \\u00faltima generaci\\u00f3n, sala de TV y un hermoso jard\\u00edn interior. La terraza posterior tiene una piscina climatizada, \\u00e1rea de barbacoa y un jard\\u00edn paisaj\\u00edstico con fuentes de agua.\\r\\n\\r\\nLa planta superior alberga las 5 habitaciones, incluyendo la suite principal con terraza privada y vista panor\\u00e1mica de la ciudad. La planta inferior tiene un \\u00e1rea de servicio con 2 habitaciones para el personal, lavander\\u00eda y un amplio garaje para 4 veh\\u00edculos.\\r\\n\\r\\nLa propiedad cuenta con sistema de seguridad 24\\/7, circuito cerrado de c\\u00e1maras, generador el\\u00e9ctrico, cisterna de agua y sistema de riego autom\\u00e1tico. Ideal para familias que buscan exclusividad, comodidad y seguridad en un entorno privilegiado.","price":"850000.00","price_currency":"USD","country_id":1,"state_id":1,"municipality_id":1,"parish_id":1,"city_id":1,"address":"Av. Principal de Altamira, Quinta Los Manantiales","location":"Venezuela, Distrito Capital, Libertador, Altagracia, Caracas, Av. Principal de Altamira, Quinta Los Manantiales","sector":null,"city":null,"state":null,"country":null,"zip_code":null,"bedrooms":5,"bathrooms":5,"parking_spaces":4,"area":"350.00","land_area":"500.00","floors":3,"year_built":2018,"type":"venta","status":"publicada","features":["aire_acondicionado","balcon","camaras_seguridad","chimenea","cisterna","jardin","piscina","porteria","terraza","zonas_verdes"],"user_id":1,"category_id":1,"views":0,"inquiries":0,"is_featured":false,"featured_until":null,"meta_data":null,"created_at":"2026-07-18T00:20:39.000000Z","updated_at":"2026-07-18T00:20:39.000000Z","deleted_at":null}	{"id":1,"title":"Casa de Lujo en Altamira con Piscina y Vista Panor\\u00e1mica","description":"Espectacular casa de lujo ubicada en la exclusiva zona de Altamira, una de las zonas m\\u00e1s prestigiosas de Caracas. Esta propiedad cuenta con 5 habitaciones, cada una con ba\\u00f1o privado y walk-in closet. La casa est\\u00e1 distribuida en 3 plantas con un dise\\u00f1o arquitect\\u00f3nico moderno y elegante.\\r\\n\\r\\nLa planta principal cuenta con un amplio sal\\u00f3n de doble altura, comedor formal, cocina totalmente equipada con electrodom\\u00e9sticos de \\u00faltima generaci\\u00f3n, sala de TV y un hermoso jard\\u00edn interior. La terraza posterior tiene una piscina climatizada, \\u00e1rea de barbacoa y un jard\\u00edn paisaj\\u00edstico con fuentes de agua.\\r\\n\\r\\nLa planta superior alberga las 5 habitaciones, incluyendo la suite principal con terraza privada y vista panor\\u00e1mica de la ciudad. La planta inferior tiene un \\u00e1rea de servicio con 2 habitaciones para el personal, lavander\\u00eda y un amplio garaje para 4 veh\\u00edculos.\\r\\n\\r\\nLa propiedad cuenta con sistema de seguridad 24\\/7, circuito cerrado de c\\u00e1maras, generador el\\u00e9ctrico, cisterna de agua y sistema de riego autom\\u00e1tico. Ideal para familias que buscan exclusividad, comodidad y seguridad en un entorno privilegiado.","price":"85000.00","price_currency":"USD","country_id":"1","state_id":"1","municipality_id":"1","parish_id":"1","city_id":"1","address":"Av. Principal de Altamira, Quinta Los Manantiales","location":"Venezuela, Distrito Capital, Libertador, Altagracia, Caracas, Av. Principal de Altamira, Quinta Los Manantiales","sector":null,"city":null,"state":null,"country":null,"zip_code":null,"bedrooms":"5","bathrooms":"5","parking_spaces":"4","area":"350.00","land_area":"500.00","floors":"3","year_built":"2018","type":"venta","status":"publicada","features":["aire_acondicionado","balcon","camaras_seguridad","chimenea","cisterna","jardin","piscina","porteria","terraza","zonas_verdes"],"user_id":1,"category_id":"1","views":0,"inquiries":0,"is_featured":false,"featured_until":null,"meta_data":null,"created_at":"2026-07-18T00:20:39.000000Z","updated_at":"2026-07-18T00:20:58.000000Z","deleted_at":null}	Carlos Rodríguez ACTUALIZÓ Property 'Casa de Lujo en Altamira con Piscina y Vista Panorámica'. Cambios: precio: '850000.00' → '85000.00'	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36	2026-07-18 00:20:58	2026-07-18 00:20:58	updated	updated	http://127.0.0.1:8000/admin/properties/1	\N	\N	\N	\N
4	1	App\\Models\\User	1	\N	\N	Cierre de sesión de Carlos	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36	2026-07-18 00:22:35	2026-07-18 00:22:35	logout	logout	http://127.0.0.1:8000/logout	\N	\N	\N	\N
5	1	App\\Models\\User	1	\N	\N	Inicio de sesión de Carlos	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36	2026-07-18 00:28:14	2026-07-18 00:28:14	login	login	http://127.0.0.1:8000/login	\N	\N	\N	\N
\.


--
-- TOC entry 5408 (class 0 OID 140154)
-- Dependencies: 225
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache (key, value, expiration) FROM stdin;
laravel-cache-api_countries_all	TzozOToiSWxsdW1pbmF0ZVxEYXRhYmFzZVxFbG9xdWVudFxDb2xsZWN0aW9uIjoyOntzOjg6IgAqAGl0ZW1zIjthOjE6e2k6MDtPOjE4OiJBcHBcTW9kZWxzXENvdW50cnkiOjMzOntzOjEzOiIAKgBjb25uZWN0aW9uIjtzOjU6InBnc3FsIjtzOjg6IgAqAHRhYmxlIjtzOjk6ImNvdW50cmllcyI7czoxMzoiACoAcHJpbWFyeUtleSI7czoyOiJpZCI7czoxMDoiACoAa2V5VHlwZSI7czozOiJpbnQiO3M6MTI6ImluY3JlbWVudGluZyI7YjoxO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjI6e3M6MjoiaWQiO2k6MTtzOjQ6Im5hbWUiO3M6OToiVmVuZXp1ZWxhIjt9czoxMToiACoAb3JpZ2luYWwiO2E6Mjp7czoyOiJpZCI7aToxO3M6NDoibmFtZSI7czo5OiJWZW5lenVlbGEiO31zOjEwOiIAKgBjaGFuZ2VzIjthOjA6e31zOjExOiIAKgBwcmV2aW91cyI7YTowOnt9czo4OiIAKgBjYXN0cyI7YTowOnt9czoxNzoiACoAY2xhc3NDYXN0Q2FjaGUiO2E6MDp7fXM6MjE6IgAqAGF0dHJpYnV0ZUNhc3RDYWNoZSI7YTowOnt9czoxMzoiACoAZGF0ZUZvcm1hdCI7TjtzOjEwOiIAKgBhcHBlbmRzIjthOjA6e31zOjE5OiIAKgBkaXNwYXRjaGVzRXZlbnRzIjthOjA6e31zOjE0OiIAKgBvYnNlcnZhYmxlcyI7YTowOnt9czoxMjoiACoAcmVsYXRpb25zIjthOjA6e31zOjEwOiIAKgB0b3VjaGVzIjthOjA6e31zOjI3OiIAKgByZWxhdGlvbkF1dG9sb2FkQ2FsbGJhY2siO047czoyNjoiACoAcmVsYXRpb25BdXRvbG9hZENvbnRleHQiO047czoxMDoidGltZXN0YW1wcyI7YjoxO3M6MTM6InVzZXNVbmlxdWVJZHMiO2I6MDtzOjk6IgAqAGhpZGRlbiI7YTowOnt9czoxMDoiACoAdmlzaWJsZSI7YTowOnt9czoxMToiACoAZmlsbGFibGUiO2E6Njp7aTowO3M6NDoibmFtZSI7aToxO3M6NDoiY29kZSI7aToyO3M6MTA6InBob25lX2NvZGUiO2k6MztzOjEyOiJwaG9uZV9mb3JtYXQiO2k6NDtzOjE2OiJwaG9uZV9taW5fbGVuZ3RoIjtpOjU7czoxNjoicGhvbmVfbWF4X2xlbmd0aCI7fXM6MTA6IgAqAGd1YXJkZWQiO2E6MTp7aTowO3M6MToiKiI7fX19czoyODoiACoAZXNjYXBlV2hlbkNhc3RpbmdUb1N0cmluZyI7YjowO30=	1784337460
laravel-cache-api_countries_list	TzozOToiSWxsdW1pbmF0ZVxEYXRhYmFzZVxFbG9xdWVudFxDb2xsZWN0aW9uIjoyOntzOjg6IgAqAGl0ZW1zIjthOjE6e2k6MDtPOjE4OiJBcHBcTW9kZWxzXENvdW50cnkiOjMzOntzOjEzOiIAKgBjb25uZWN0aW9uIjtzOjU6InBnc3FsIjtzOjg6IgAqAHRhYmxlIjtzOjk6ImNvdW50cmllcyI7czoxMzoiACoAcHJpbWFyeUtleSI7czoyOiJpZCI7czoxMDoiACoAa2V5VHlwZSI7czozOiJpbnQiO3M6MTI6ImluY3JlbWVudGluZyI7YjoxO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjI6e3M6MjoiaWQiO2k6MTtzOjQ6Im5hbWUiO3M6OToiVmVuZXp1ZWxhIjt9czoxMToiACoAb3JpZ2luYWwiO2E6Mjp7czoyOiJpZCI7aToxO3M6NDoibmFtZSI7czo5OiJWZW5lenVlbGEiO31zOjEwOiIAKgBjaGFuZ2VzIjthOjA6e31zOjExOiIAKgBwcmV2aW91cyI7YTowOnt9czo4OiIAKgBjYXN0cyI7YTowOnt9czoxNzoiACoAY2xhc3NDYXN0Q2FjaGUiO2E6MDp7fXM6MjE6IgAqAGF0dHJpYnV0ZUNhc3RDYWNoZSI7YTowOnt9czoxMzoiACoAZGF0ZUZvcm1hdCI7TjtzOjEwOiIAKgBhcHBlbmRzIjthOjA6e31zOjE5OiIAKgBkaXNwYXRjaGVzRXZlbnRzIjthOjA6e31zOjE0OiIAKgBvYnNlcnZhYmxlcyI7YTowOnt9czoxMjoiACoAcmVsYXRpb25zIjthOjA6e31zOjEwOiIAKgB0b3VjaGVzIjthOjA6e31zOjI3OiIAKgByZWxhdGlvbkF1dG9sb2FkQ2FsbGJhY2siO047czoyNjoiACoAcmVsYXRpb25BdXRvbG9hZENvbnRleHQiO047czoxMDoidGltZXN0YW1wcyI7YjoxO3M6MTM6InVzZXNVbmlxdWVJZHMiO2I6MDtzOjk6IgAqAGhpZGRlbiI7YTowOnt9czoxMDoiACoAdmlzaWJsZSI7YTowOnt9czoxMToiACoAZmlsbGFibGUiO2E6Njp7aTowO3M6NDoibmFtZSI7aToxO3M6NDoiY29kZSI7aToyO3M6MTA6InBob25lX2NvZGUiO2k6MztzOjEyOiJwaG9uZV9mb3JtYXQiO2k6NDtzOjE2OiJwaG9uZV9taW5fbGVuZ3RoIjtpOjU7czoxNjoicGhvbmVfbWF4X2xlbmd0aCI7fXM6MTA6IgAqAGd1YXJkZWQiO2E6MTp7aTowO3M6MToiKiI7fX19czoyODoiACoAZXNjYXBlV2hlbkNhc3RpbmdUb1N0cmluZyI7YjowO30=	1784420260
laravel-cache-api_phone_codes	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	1784420261
laravel-cache-countries_list	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	1784420264
laravel-cache-api_states_1	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	1784337569
laravel-cache-api_municipalities_1	TzozOToiSWxsdW1pbmF0ZVxEYXRhYmFzZVxFbG9xdWVudFxDb2xsZWN0aW9uIjoyOntzOjg6IgAqAGl0ZW1zIjthOjE6e2k6MDtPOjIzOiJBcHBcTW9kZWxzXE11bmljaXBhbGl0eSI6MzM6e3M6MTM6IgAqAGNvbm5lY3Rpb24iO3M6NToicGdzcWwiO3M6ODoiACoAdGFibGUiO3M6MTQ6Im11bmljaXBhbGl0aWVzIjtzOjEzOiIAKgBwcmltYXJ5S2V5IjtzOjI6ImlkIjtzOjEwOiIAKgBrZXlUeXBlIjtzOjM6ImludCI7czoxMjoiaW5jcmVtZW50aW5nIjtiOjE7czo3OiIAKgB3aXRoIjthOjA6e31zOjEyOiIAKgB3aXRoQ291bnQiO2E6MDp7fXM6MTk6InByZXZlbnRzTGF6eUxvYWRpbmciO2I6MDtzOjEwOiIAKgBwZXJQYWdlIjtpOjE1O3M6NjoiZXhpc3RzIjtiOjE7czoxODoid2FzUmVjZW50bHlDcmVhdGVkIjtiOjA7czoyODoiACoAZXNjYXBlV2hlbkNhc3RpbmdUb1N0cmluZyI7YjowO3M6MTM6IgAqAGF0dHJpYnV0ZXMiO2E6Mjp7czoyOiJpZCI7aToxO3M6NDoibmFtZSI7czoxMDoiTGliZXJ0YWRvciI7fXM6MTE6IgAqAG9yaWdpbmFsIjthOjI6e3M6MjoiaWQiO2k6MTtzOjQ6Im5hbWUiO3M6MTA6IkxpYmVydGFkb3IiO31zOjEwOiIAKgBjaGFuZ2VzIjthOjA6e31zOjExOiIAKgBwcmV2aW91cyI7YTowOnt9czo4OiIAKgBjYXN0cyI7YTowOnt9czoxNzoiACoAY2xhc3NDYXN0Q2FjaGUiO2E6MDp7fXM6MjE6IgAqAGF0dHJpYnV0ZUNhc3RDYWNoZSI7YTowOnt9czoxMzoiACoAZGF0ZUZvcm1hdCI7TjtzOjEwOiIAKgBhcHBlbmRzIjthOjA6e31zOjE5OiIAKgBkaXNwYXRjaGVzRXZlbnRzIjthOjA6e31zOjE0OiIAKgBvYnNlcnZhYmxlcyI7YTowOnt9czoxMjoiACoAcmVsYXRpb25zIjthOjA6e31zOjEwOiIAKgB0b3VjaGVzIjthOjA6e31zOjI3OiIAKgByZWxhdGlvbkF1dG9sb2FkQ2FsbGJhY2siO047czoyNjoiACoAcmVsYXRpb25BdXRvbG9hZENvbnRleHQiO047czoxMDoidGltZXN0YW1wcyI7YjoxO3M6MTM6InVzZXNVbmlxdWVJZHMiO2I6MDtzOjk6IgAqAGhpZGRlbiI7YTowOnt9czoxMDoiACoAdmlzaWJsZSI7YTowOnt9czoxMToiACoAZmlsbGFibGUiO2E6Mjp7aTowO3M6NDoibmFtZSI7aToxO3M6ODoic3RhdGVfaWQiO31zOjEwOiIAKgBndWFyZGVkIjthOjE6e2k6MDtzOjE6IioiO319fXM6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDt9	1784337570
laravel-cache-api_parishes_1	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	1784337572
laravel-cache-api_cities_1	TzozOToiSWxsdW1pbmF0ZVxEYXRhYmFzZVxFbG9xdWVudFxDb2xsZWN0aW9uIjoyOntzOjg6IgAqAGl0ZW1zIjthOjE6e2k6MDtPOjE1OiJBcHBcTW9kZWxzXENpdHkiOjMzOntzOjEzOiIAKgBjb25uZWN0aW9uIjtzOjU6InBnc3FsIjtzOjg6IgAqAHRhYmxlIjtzOjY6ImNpdGllcyI7czoxMzoiACoAcHJpbWFyeUtleSI7czoyOiJpZCI7czoxMDoiACoAa2V5VHlwZSI7czozOiJpbnQiO3M6MTI6ImluY3JlbWVudGluZyI7YjoxO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjI6e3M6MjoiaWQiO2k6MTtzOjQ6Im5hbWUiO3M6NzoiQ2FyYWNhcyI7fXM6MTE6IgAqAG9yaWdpbmFsIjthOjI6e3M6MjoiaWQiO2k6MTtzOjQ6Im5hbWUiO3M6NzoiQ2FyYWNhcyI7fXM6MTA6IgAqAGNoYW5nZXMiO2E6MDp7fXM6MTE6IgAqAHByZXZpb3VzIjthOjA6e31zOjg6IgAqAGNhc3RzIjthOjA6e31zOjE3OiIAKgBjbGFzc0Nhc3RDYWNoZSI7YTowOnt9czoyMToiACoAYXR0cmlidXRlQ2FzdENhY2hlIjthOjA6e31zOjEzOiIAKgBkYXRlRm9ybWF0IjtOO3M6MTA6IgAqAGFwcGVuZHMiO2E6MDp7fXM6MTk6IgAqAGRpc3BhdGNoZXNFdmVudHMiO2E6MDp7fXM6MTQ6IgAqAG9ic2VydmFibGVzIjthOjA6e31zOjEyOiIAKgByZWxhdGlvbnMiO2E6MDp7fXM6MTA6IgAqAHRvdWNoZXMiO2E6MDp7fXM6Mjc6IgAqAHJlbGF0aW9uQXV0b2xvYWRDYWxsYmFjayI7TjtzOjI2OiIAKgByZWxhdGlvbkF1dG9sb2FkQ29udGV4dCI7TjtzOjEwOiJ0aW1lc3RhbXBzIjtiOjE7czoxMzoidXNlc1VuaXF1ZUlkcyI7YjowO3M6OToiACoAaGlkZGVuIjthOjA6e31zOjEwOiIAKgB2aXNpYmxlIjthOjA6e31zOjExOiIAKgBmaWxsYWJsZSI7YToyOntpOjA7czo0OiJuYW1lIjtpOjE7czo5OiJwYXJpc2hfaWQiO31zOjEwOiIAKgBndWFyZGVkIjthOjE6e2k6MDtzOjE6IioiO319fXM6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDt9	1784337573
laravel-cache-5c785c036466adea360111aa28563bfd556b5fba:timer	i:1784334552;	1784334552
laravel-cache-5c785c036466adea360111aa28563bfd556b5fba	i:1;	1784334552
laravel-cache-spatie.permission.cache	a:3:{s:5:"alias";a:4:{s:1:"a";s:2:"id";s:1:"b";s:4:"name";s:1:"c";s:10:"guard_name";s:1:"r";s:5:"roles";}s:11:"permissions";a:51:{i:0;a:4:{s:1:"a";i:1;s:1:"b";s:20:"ver panel de control";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:1;a:4:{s:1:"a";i:2;s:1:"b";s:12:"ver usuarios";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:2;a:4:{s:1:"a";i:3;s:1:"b";s:13:"crear usuario";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:3;a:4:{s:1:"a";i:4;s:1:"b";s:14:"editar usuario";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:4;a:4:{s:1:"a";i:5;s:1:"b";s:16:"eliminar usuario";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:5;a:4:{s:1:"a";i:6;s:1:"b";s:9:"ver roles";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:6;a:4:{s:1:"a";i:7;s:1:"b";s:9:"crear rol";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:7;a:4:{s:1:"a";i:8;s:1:"b";s:10:"editar rol";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:8;a:4:{s:1:"a";i:9;s:1:"b";s:12:"eliminar rol";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:9;a:4:{s:1:"a";i:10;s:1:"b";s:14:"ver categorias";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:10;a:4:{s:1:"a";i:11;s:1:"b";s:15:"crear categoria";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:11;a:4:{s:1:"a";i:12;s:1:"b";s:16:"editar categoria";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:12;a:4:{s:1:"a";i:13;s:1:"b";s:18:"eliminar categoria";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:13;a:4:{s:1:"a";i:14;s:1:"b";s:10:"ver paises";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:14;a:4:{s:1:"a";i:15;s:1:"b";s:12:"crear paises";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:15;a:4:{s:1:"a";i:16;s:1:"b";s:15:"eliminar paises";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:16;a:4:{s:1:"a";i:17;s:1:"b";s:11:"ver estados";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:17;a:4:{s:1:"a";i:18;s:1:"b";s:13:"crear estados";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:18;a:4:{s:1:"a";i:19;s:1:"b";s:16:"eliminar estados";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:19;a:4:{s:1:"a";i:20;s:1:"b";s:14:"ver municipios";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:20;a:4:{s:1:"a";i:21;s:1:"b";s:16:"crear municipios";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:21;a:4:{s:1:"a";i:22;s:1:"b";s:19:"eliminar municipios";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:22;a:4:{s:1:"a";i:23;s:1:"b";s:14:"ver parroquias";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:23;a:4:{s:1:"a";i:24;s:1:"b";s:16:"crear parroquias";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:24;a:4:{s:1:"a";i:25;s:1:"b";s:19:"eliminar parroquias";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:25;a:4:{s:1:"a";i:26;s:1:"b";s:12:"ver ciudades";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:26;a:4:{s:1:"a";i:27;s:1:"b";s:14:"crear ciudades";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:27;a:4:{s:1:"a";i:28;s:1:"b";s:17:"eliminar ciudades";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:28;a:4:{s:1:"a";i:29;s:1:"b";s:15:"ver propiedades";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:29;a:4:{s:1:"a";i:30;s:1:"b";s:15:"crear propiedad";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:30;a:4:{s:1:"a";i:31;s:1:"b";s:16:"editar propiedad";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:31;a:4:{s:1:"a";i:32;s:1:"b";s:18:"eliminar propiedad";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:32;a:4:{s:1:"a";i:33;s:1:"b";s:18:"publicar propiedad";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:33;a:4:{s:1:"a";i:34;s:1:"b";s:9:"ver citas";s:1:"c";s:3:"web";s:1:"r";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:34;a:4:{s:1:"a";i:35;s:1:"b";s:10:"crear cita";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:5;}}i:35;a:4:{s:1:"a";i:36;s:1:"b";s:11:"editar cita";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:36;a:4:{s:1:"a";i:37;s:1:"b";s:13:"eliminar cita";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:37;a:4:{s:1:"a";i:38;s:1:"b";s:9:"ver leads";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:38;a:4:{s:1:"a";i:39;s:1:"b";s:10:"crear lead";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:39;a:4:{s:1:"a";i:40;s:1:"b";s:11:"editar lead";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:40;a:4:{s:1:"a";i:41;s:1:"b";s:13:"eliminar lead";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:41;a:4:{s:1:"a";i:42;s:1:"b";s:13:"ver servicios";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:42;a:4:{s:1:"a";i:43;s:1:"b";s:15:"crear servicios";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:43;a:4:{s:1:"a";i:44;s:1:"b";s:16:"editar servicios";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:44;a:4:{s:1:"a";i:45;s:1:"b";s:18:"eliminar servicios";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:45;a:4:{s:1:"a";i:46;s:1:"b";s:21:"ver logs de auditoria";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:4;}}i:46;a:4:{s:1:"a";i:47;s:1:"b";s:12:"ver reportes";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:47;a:4:{s:1:"a";i:48;s:1:"b";s:17:"exportar reportes";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:48;a:4:{s:1:"a";i:49;s:1:"b";s:18:"ver configuración";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:49;a:4:{s:1:"a";i:50;s:1:"b";s:21:"editar configuración";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:50;a:4:{s:1:"a";i:51;s:1:"b";s:35:"actualizar configuracion telefonica";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}}s:5:"roles";a:5:{i:0;a:3:{s:1:"a";i:1;s:1:"b";s:11:"Super Admin";s:1:"c";s:3:"web";}i:1;a:3:{s:1:"a";i:2;s:1:"b";s:13:"Administrador";s:1:"c";s:3:"web";}i:2;a:3:{s:1:"a";i:3;s:1:"b";s:19:"Asesor Inmobiliario";s:1:"c";s:3:"web";}i:3;a:3:{s:1:"a";i:4;s:1:"b";s:7:"Auditor";s:1:"c";s:3:"web";}i:4;a:3:{s:1:"a";i:5;s:1:"b";s:7:"Cliente";s:1:"c";s:3:"web";}}}	1784420933
laravel-cache-356a192b7913b04c54574d18c28d46e6395428ab:timer	i:1784336979;	1784336979
laravel-cache-356a192b7913b04c54574d18c28d46e6395428ab	i:1;	1784336979
laravel-cache-user_roles_1	a:1:{i:0;s:11:"Super Admin";}	1784338308
\.


--
-- TOC entry 5409 (class 0 OID 140165)
-- Dependencies: 226
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- TOC entry 5426 (class 0 OID 140295)
-- Dependencies: 243
-- Data for Name: categories; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.categories (id, name, slug, description, icon, is_active, "order", created_at, updated_at) FROM stdin;
1	Casa	casa	casa	ph ph-house	t	1	2026-07-18 00:18:44	2026-07-18 00:18:44
\.


--
-- TOC entry 5424 (class 0 OID 140280)
-- Dependencies: 241
-- Data for Name: cities; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cities (id, parish_id, name, created_at, updated_at) FROM stdin;
1	1	Caracas	2026-07-18 00:14:25	2026-07-18 00:14:25
2	2	Caracas	2026-07-18 00:14:25	2026-07-18 00:14:25
3	3	Baruta	2026-07-18 00:14:25	2026-07-18 00:14:25
4	4	Valencia	2026-07-18 00:14:25	2026-07-18 00:14:25
\.


--
-- TOC entry 5438 (class 0 OID 140549)
-- Dependencies: 255
-- Data for Name: conversations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.conversations (id, client_id, asesor_id, subject, last_message_at, is_active, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5416 (class 0 OID 140226)
-- Dependencies: 233
-- Data for Name: countries; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.countries (id, name, code, created_at, updated_at, phone_code, phone_format, phone_min_length, phone_max_length) FROM stdin;
1	Venezuela	VEN	2026-07-18 00:14:25	2026-07-18 00:14:25	+58	000-0000000	10	10
\.


--
-- TOC entry 5414 (class 0 OID 140207)
-- Dependencies: 231
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- TOC entry 5434 (class 0 OID 140473)
-- Dependencies: 251
-- Data for Name: favorites; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.favorites (id, user_id, property_id, created_at, updated_at) FROM stdin;
1	1	1	2026-07-18 00:21:33	2026-07-18 00:21:33
\.


--
-- TOC entry 5412 (class 0 OID 140192)
-- Dependencies: 229
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- TOC entry 5411 (class 0 OID 140177)
-- Dependencies: 228
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- TOC entry 5436 (class 0 OID 140499)
-- Dependencies: 253
-- Data for Name: leads; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.leads (id, user_id, property_id, asesor_id, name, email, phone, id_type, id_number, source, source_detail, interest_type, budget_min, budget_max, preferences, status, notes, last_contact, contact_count, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5440 (class 0 OID 140580)
-- Dependencies: 257
-- Data for Name: messages; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.messages (id, conversation_id, user_id, content, is_read, read_at, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5403 (class 0 OID 140088)
-- Dependencies: 220
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	0001_01_01_000001_create_cache_table	1
3	0001_01_01_000002_create_jobs_table	1
4	2026_03_08_110106_create_countries_table	1
5	2026_03_08_110115_create_states_table	1
6	2026_03_08_110124_create_municipalities_table	1
7	2026_03_08_110130_create_parishes_table	1
8	2026_03_08_110137_create_cities_table	1
9	2026_03_09_195518_create_categories_table	1
10	2026_03_09_195545_create_properties_table	1
11	2026_03_09_195612_create_property_images_table	1
12	2026_03_09_195638_create_appointments_table	1
13	2026_03_09_195700_create_favorites_table	1
14	2026_03_09_195722_create_leads_table	1
15	2026_03_09_195742_create_conversations_table	1
16	2026_03_09_195802_create_messages_table	1
17	2026_03_09_195820_create_settings_table	1
18	2026_03_09_195840_create_audit_logs_table	1
19	2026_03_09_200049_create_notifications_table	1
20	2026_03_09_200639_create_personal_access_tokens_table	1
21	2026_03_09_200647_create_permission_tables	1
22	2026_03_25_020038_add_soft_deletes_to_users_table	1
23	2026_04_12_032519_create_account_reactivation_tokens_table	1
24	2026_04_12_152224_add_phone_code_to_countries_table	1
25	2026_06_06_201857_create_user_notifications_table	1
26	2026_06_08_053138_create_report_snapshots_table	1
27	2026_06_08_165415_fix_missing_audit_logs_action_column	1
28	2026_06_18_154630_create_site_configurations_table	1
29	2026_06_21_220255_create_appointment_settings_table	1
30	2026_06_23_131318_create_audits_table	1
31	2026_06_23_181559_create_services_table	1
32	2026_06_24_194659_add_missing_columns_to_audit_logs_table	1
\.


--
-- TOC entry 5453 (class 0 OID 140714)
-- Dependencies: 270
-- Data for Name: model_has_permissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.model_has_permissions (permission_id, model_type, model_id) FROM stdin;
\.


--
-- TOC entry 5454 (class 0 OID 140728)
-- Dependencies: 271
-- Data for Name: model_has_roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.model_has_roles (role_id, model_type, model_id) FROM stdin;
1	App\\Models\\User	1
2	App\\Models\\User	2
3	App\\Models\\User	3
3	App\\Models\\User	4
4	App\\Models\\User	5
5	App\\Models\\User	6
5	App\\Models\\User	7
\.


--
-- TOC entry 5420 (class 0 OID 140250)
-- Dependencies: 237
-- Data for Name: municipalities; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.municipalities (id, state_id, name, created_at, updated_at) FROM stdin;
1	1	Libertador	2026-07-18 00:14:25	2026-07-18 00:14:25
2	2	Baruta	2026-07-18 00:14:25	2026-07-18 00:14:25
3	3	Valencia	2026-07-18 00:14:25	2026-07-18 00:14:25
\.


--
-- TOC entry 5422 (class 0 OID 140265)
-- Dependencies: 239
-- Data for Name: parishes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.parishes (id, municipality_id, name, created_at, updated_at) FROM stdin;
1	1	Altagracia	2026-07-18 00:14:25	2026-07-18 00:14:25
2	1	Catedral	2026-07-18 00:14:25	2026-07-18 00:14:25
3	2	Baruta	2026-07-18 00:14:25	2026-07-18 00:14:25
4	3	San José	2026-07-18 00:14:25	2026-07-18 00:14:25
\.


--
-- TOC entry 5406 (class 0 OID 140133)
-- Dependencies: 223
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- TOC entry 5450 (class 0 OID 140687)
-- Dependencies: 267
-- Data for Name: permissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.permissions (id, name, guard_name, created_at, updated_at) FROM stdin;
1	ver panel de control	web	2026-07-18 00:14:25	2026-07-18 00:14:25
2	ver usuarios	web	2026-07-18 00:14:25	2026-07-18 00:14:25
3	crear usuario	web	2026-07-18 00:14:25	2026-07-18 00:14:25
4	editar usuario	web	2026-07-18 00:14:25	2026-07-18 00:14:25
5	eliminar usuario	web	2026-07-18 00:14:25	2026-07-18 00:14:25
6	ver roles	web	2026-07-18 00:14:25	2026-07-18 00:14:25
7	crear rol	web	2026-07-18 00:14:25	2026-07-18 00:14:25
8	editar rol	web	2026-07-18 00:14:25	2026-07-18 00:14:25
9	eliminar rol	web	2026-07-18 00:14:25	2026-07-18 00:14:25
10	ver categorias	web	2026-07-18 00:14:25	2026-07-18 00:14:25
11	crear categoria	web	2026-07-18 00:14:25	2026-07-18 00:14:25
12	editar categoria	web	2026-07-18 00:14:25	2026-07-18 00:14:25
13	eliminar categoria	web	2026-07-18 00:14:25	2026-07-18 00:14:25
14	ver paises	web	2026-07-18 00:14:25	2026-07-18 00:14:25
15	crear paises	web	2026-07-18 00:14:25	2026-07-18 00:14:25
16	eliminar paises	web	2026-07-18 00:14:25	2026-07-18 00:14:25
17	ver estados	web	2026-07-18 00:14:25	2026-07-18 00:14:25
18	crear estados	web	2026-07-18 00:14:25	2026-07-18 00:14:25
19	eliminar estados	web	2026-07-18 00:14:25	2026-07-18 00:14:25
20	ver municipios	web	2026-07-18 00:14:25	2026-07-18 00:14:25
21	crear municipios	web	2026-07-18 00:14:25	2026-07-18 00:14:25
22	eliminar municipios	web	2026-07-18 00:14:25	2026-07-18 00:14:25
23	ver parroquias	web	2026-07-18 00:14:25	2026-07-18 00:14:25
24	crear parroquias	web	2026-07-18 00:14:25	2026-07-18 00:14:25
25	eliminar parroquias	web	2026-07-18 00:14:25	2026-07-18 00:14:25
26	ver ciudades	web	2026-07-18 00:14:25	2026-07-18 00:14:25
27	crear ciudades	web	2026-07-18 00:14:25	2026-07-18 00:14:25
28	eliminar ciudades	web	2026-07-18 00:14:25	2026-07-18 00:14:25
29	ver propiedades	web	2026-07-18 00:14:25	2026-07-18 00:14:25
30	crear propiedad	web	2026-07-18 00:14:25	2026-07-18 00:14:25
31	editar propiedad	web	2026-07-18 00:14:25	2026-07-18 00:14:25
32	eliminar propiedad	web	2026-07-18 00:14:25	2026-07-18 00:14:25
33	publicar propiedad	web	2026-07-18 00:14:25	2026-07-18 00:14:25
34	ver citas	web	2026-07-18 00:14:25	2026-07-18 00:14:25
35	crear cita	web	2026-07-18 00:14:25	2026-07-18 00:14:25
36	editar cita	web	2026-07-18 00:14:25	2026-07-18 00:14:25
37	eliminar cita	web	2026-07-18 00:14:25	2026-07-18 00:14:25
38	ver leads	web	2026-07-18 00:14:25	2026-07-18 00:14:25
39	crear lead	web	2026-07-18 00:14:25	2026-07-18 00:14:25
40	editar lead	web	2026-07-18 00:14:25	2026-07-18 00:14:25
41	eliminar lead	web	2026-07-18 00:14:25	2026-07-18 00:14:25
42	ver servicios	web	2026-07-18 00:14:25	2026-07-18 00:14:25
43	crear servicios	web	2026-07-18 00:14:25	2026-07-18 00:14:25
44	editar servicios	web	2026-07-18 00:14:25	2026-07-18 00:14:25
45	eliminar servicios	web	2026-07-18 00:14:25	2026-07-18 00:14:25
46	ver logs de auditoria	web	2026-07-18 00:14:25	2026-07-18 00:14:25
47	ver reportes	web	2026-07-18 00:14:25	2026-07-18 00:14:25
48	exportar reportes	web	2026-07-18 00:14:25	2026-07-18 00:14:25
49	ver configuración	web	2026-07-18 00:14:25	2026-07-18 00:14:25
50	editar configuración	web	2026-07-18 00:14:25	2026-07-18 00:14:25
51	actualizar configuracion telefonica	web	2026-07-18 00:14:25	2026-07-18 00:14:25
\.


--
-- TOC entry 5448 (class 0 OID 140669)
-- Dependencies: 265
-- Data for Name: personal_access_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.personal_access_tokens (id, tokenable_type, tokenable_id, name, token, abilities, last_used_at, expires_at, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5428 (class 0 OID 140313)
-- Dependencies: 245
-- Data for Name: properties; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.properties (id, title, description, price, price_currency, country_id, state_id, municipality_id, parish_id, city_id, address, location, sector, city, state, country, zip_code, bedrooms, bathrooms, parking_spaces, area, land_area, floors, year_built, type, status, features, user_id, category_id, views, inquiries, is_featured, featured_until, meta_data, created_at, updated_at, deleted_at) FROM stdin;
1	Casa de Lujo en Altamira con Piscina y Vista Panorámica	Espectacular casa de lujo ubicada en la exclusiva zona de Altamira, una de las zonas más prestigiosas de Caracas. Esta propiedad cuenta con 5 habitaciones, cada una con baño privado y walk-in closet. La casa está distribuida en 3 plantas con un diseño arquitectónico moderno y elegante.\r\n\r\nLa planta principal cuenta con un amplio salón de doble altura, comedor formal, cocina totalmente equipada con electrodomésticos de última generación, sala de TV y un hermoso jardín interior. La terraza posterior tiene una piscina climatizada, área de barbacoa y un jardín paisajístico con fuentes de agua.\r\n\r\nLa planta superior alberga las 5 habitaciones, incluyendo la suite principal con terraza privada y vista panorámica de la ciudad. La planta inferior tiene un área de servicio con 2 habitaciones para el personal, lavandería y un amplio garaje para 4 vehículos.\r\n\r\nLa propiedad cuenta con sistema de seguridad 24/7, circuito cerrado de cámaras, generador eléctrico, cisterna de agua y sistema de riego automático. Ideal para familias que buscan exclusividad, comodidad y seguridad en un entorno privilegiado.	85000.00	USD	1	1	1	1	1	Av. Principal de Altamira, Quinta Los Manantiales	Venezuela, Distrito Capital, Libertador, Altagracia, Caracas, Av. Principal de Altamira, Quinta Los Manantiales	\N	\N	\N	\N	\N	5	5	4	350.00	500.00	3	2018	venta	publicada	["aire_acondicionado","balcon","camaras_seguridad","chimenea","cisterna","jardin","piscina","porteria","terraza","zonas_verdes"]	1	1	1	0	f	\N	\N	2026-07-18 00:20:39	2026-07-18 00:21:43	\N
\.


--
-- TOC entry 5430 (class 0 OID 140401)
-- Dependencies: 247
-- Data for Name: property_images; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.property_images (id, property_id, image_path, thumbnail_path, caption, "order", is_primary, mime_type, size, created_at, updated_at) FROM stdin;
1	1	properties/zjXNPbyWedGopXERz10eWItYenZpRqdY8S83tTGE.jpg	properties/thumbnails/thumb_6a5ac6da5b38c.jpg	\N	0	t	image/jpeg	147413	2026-07-18 00:20:42	2026-07-18 00:20:42
2	1	properties/fBHdtmzZvUHsY94aXuv5H35PgGahUROMTSrvsLjz.jpg	properties/thumbnails/thumb_6a5ac6da86ede.jpg	\N	1	f	image/jpeg	113317	2026-07-18 00:20:42	2026-07-18 00:20:42
3	1	properties/WLJj5aYBvOr7z8yEcexRE61fRDGrMunFzGIAAezr.jpg	properties/thumbnails/thumb_6a5ac6da9ae93.jpg	\N	2	f	image/jpeg	171311	2026-07-18 00:20:42	2026-07-18 00:20:42
\.


--
-- TOC entry 5459 (class 0 OID 140777)
-- Dependencies: 276
-- Data for Name: report_snapshots; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.report_snapshots (id, user_id, report_type, period, data, file_path, status, sent_at, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5455 (class 0 OID 140742)
-- Dependencies: 272
-- Data for Name: role_has_permissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.role_has_permissions (permission_id, role_id) FROM stdin;
1	1
2	1
3	1
4	1
5	1
6	1
7	1
8	1
9	1
10	1
11	1
12	1
13	1
14	1
15	1
16	1
17	1
18	1
19	1
20	1
21	1
22	1
23	1
24	1
25	1
26	1
27	1
28	1
29	1
30	1
31	1
32	1
33	1
34	1
35	1
36	1
37	1
38	1
39	1
40	1
41	1
42	1
43	1
44	1
45	1
46	1
47	1
48	1
49	1
50	1
51	1
1	2
2	2
3	2
4	2
10	2
11	2
12	2
13	2
14	2
17	2
20	2
23	2
26	2
29	2
30	2
31	2
32	2
33	2
34	2
35	2
36	2
37	2
38	2
39	2
40	2
41	2
42	2
43	2
44	2
45	2
47	2
48	2
49	2
50	2
51	2
1	3
29	3
30	3
31	3
33	3
34	3
35	3
36	3
38	3
39	3
40	3
1	4
2	4
10	4
29	4
34	4
38	4
42	4
46	4
47	4
48	4
34	5
35	5
\.


--
-- TOC entry 5452 (class 0 OID 140701)
-- Dependencies: 269
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.roles (id, name, guard_name, created_at, updated_at) FROM stdin;
1	Super Admin	web	2026-07-18 00:14:25	2026-07-18 00:14:25
2	Administrador	web	2026-07-18 00:14:25	2026-07-18 00:14:25
3	Asesor Inmobiliario	web	2026-07-18 00:14:25	2026-07-18 00:14:25
4	Auditor	web	2026-07-18 00:14:25	2026-07-18 00:14:25
5	Cliente	web	2026-07-18 00:14:25	2026-07-18 00:14:25
\.


--
-- TOC entry 5467 (class 0 OID 140886)
-- Dependencies: 284
-- Data for Name: service_galleries; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.service_galleries (id, service_id, image_path, title, alt_text, "order", is_active, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5465 (class 0 OID 140864)
-- Dependencies: 282
-- Data for Name: services; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.services (id, title, slug, description, icon, color, badge, image, features, external_url, "order", is_active, is_featured, created_at, updated_at, deleted_at) FROM stdin;
\.


--
-- TOC entry 5407 (class 0 OID 140142)
-- Dependencies: 224
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
GN3xHljaXzp1LZfzRGjgRisCZbZkFy20zBYmmXrT	1	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36	YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWDlMVURjNjBDbFJtZkFtUk1henVNaGlFQWdjVE41ejZ4TG5NRWtmSSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDg6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9ub3RpZmljYXRpb25zL3VucmVhZC1jb3VudCI7czo1OiJyb3V0ZSI7czoyNToibm90aWZpY2F0aW9ucy51bnJlYWRDb3VudCI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==	1784338129
\.


--
-- TOC entry 5442 (class 0 OID 140612)
-- Dependencies: 259
-- Data for Name: settings; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.settings (id, key, value, type, "group", label, description, "order", created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5461 (class 0 OID 140804)
-- Dependencies: 278
-- Data for Name: site_configurations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.site_configurations (id, hero_badge, hero_title_line1, hero_title_line2, hero_subtitle, hero_images, hero_image_paths, featured_badge, featured_title, featured_properties, support_whatsapp, support_instagram, support_phone, support_email, created_at, updated_at) FROM stdin;
1	Exclusividad & Confort	El Arte de	Vivir Bien	Descubre una curaduría exclusiva de propiedades de lujo en las mejores zonas de Venezuela.	["https:\\/\\/images.unsplash.com\\/photo-1600596542815-2495db0c5903?q=80&w=2000&auto=format&fit=crop","https:\\/\\/images.unsplash.com\\/photo-1600607687939-ce8a6c25118c?q=80&w=2000&auto=format&fit=crop","https:\\/\\/images.unsplash.com\\/photo-1600585154340-be6161a56a0c?q=80&w=2000&auto=format&fit=crop"]	\N	Colección Exclusiva	Propiedades Destacadas	[]	58XXXXXXXXX	msoinmobiliaria	\N	\N	2026-07-18 00:14:50	2026-07-18 00:14:50
\.


--
-- TOC entry 5418 (class 0 OID 140235)
-- Dependencies: 235
-- Data for Name: states; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.states (id, country_id, name, created_at, updated_at) FROM stdin;
1	1	Distrito Capital	2026-07-18 00:14:25	2026-07-18 00:14:25
2	1	Miranda	2026-07-18 00:14:25	2026-07-18 00:14:25
3	1	Carabobo	2026-07-18 00:14:25	2026-07-18 00:14:25
\.


--
-- TOC entry 5446 (class 0 OID 140648)
-- Dependencies: 263
-- Data for Name: user_notifications; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.user_notifications (id, user_id, title, message, type, url, data, read_at, created_at, updated_at) FROM stdin;
1	2	Nueva Propiedad Creada	El asesor Carlos ha creado una nueva propiedad: Casa de Lujo en Altamira con Piscina y Vista Panorámica	info	http://127.0.0.1:8000/asesor/properties/1	{"property_id":1}	\N	2026-07-18 00:20:42	2026-07-18 00:20:42
2	5	Nueva Propiedad Creada	El asesor Carlos ha creado una nueva propiedad: Casa de Lujo en Altamira con Piscina y Vista Panorámica	info	http://127.0.0.1:8000/asesor/properties/1	{"property_id":1}	\N	2026-07-18 00:20:42	2026-07-18 00:20:42
3	5	Propiedad Actualizada	La propiedad Casa de Lujo en Altamira con Piscina y Vista Panorámica ha sido actualizada por Carlos	warning	http://127.0.0.1:8000/asesor/properties/1	{"property_id":1}	\N	2026-07-18 00:20:58	2026-07-18 00:20:58
\.


--
-- TOC entry 5405 (class 0 OID 140098)
-- Dependencies: 222
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, name, last_name, email, email_verified_at, password, remember_token, phone, profile_photo, bio, specialization, social_links, id_type, id_number, country_id, state_id, municipality_id, parish_id, city_id, address, security_questions, security_answer_1, security_answer_2, security_answer_3, security_questions_set_at, is_active, is_online, last_seen_at, created_at, updated_at, deleted_at) FROM stdin;
1	Carlos	Rodríguez	superadmin@mso.com	2026-07-18 00:14:25	$2y$12$uc5UWvzaCJWM0aI6Q2dOr.xVxgx0y71i5K.VoWUjELZ/Bm770vPGy	\N	+58 412 1234567	\N	Super Administrador de MSO Grupo Inmobiliario.	Dirección General	\N	V	12345678	1	1	1	1	1	\N	["\\u00bfEn qu\\u00e9 ciudad naciste?","\\u00bfCu\\u00e1l es el nombre de tu t\\u00edo favorito?","\\u00bfCu\\u00e1l es el nombre de tu primer amor?"]	$2y$12$hX5B3umcsTRcpGiqZy.9i.ApuBV7u0OypAQyOIAct06Iy6zmT1YCi	$2y$12$TztcIx0/weSd1PtbZWB2feizDJjOfgncJkTtsHe4ENHUlTYNPn0Tm	$2y$12$ch3vwsxRlBjtX46DAUXpZ.5sIhA6ZdC16rS28uth1S4TnnVsFWwsO	2026-07-18 00:14:26	t	f	\N	2026-07-18 00:14:26	2026-07-18 00:14:26	\N
2	María	González	admin@mso.com	2026-07-18 00:14:26	$2y$12$0JXGcpNA1fVgLdYQ.0e9neBCrNzpRJLkBHAh7/VF60WOzULE23.GW	\N	+58 414 2345678	\N	Administradora con más de 5 años de experiencia en el sector inmobiliario.	Gestión de Propiedades	\N	V	23456789	1	2	2	3	3	\N	["\\u00bfCu\\u00e1l es el nombre de tu padre?","\\u00bfCu\\u00e1l es el nombre de tu abuelo favorito?","\\u00bfCu\\u00e1l es tu deporte favorito?"]	$2y$12$tHy1AOtMyXx1xczvGadnK.FnRCQqEmsNG0/wcFv4uO0Obrf0Jxpfa	$2y$12$RIeLO.ISlpOffsA0fAq8xedtNGkmM6ZZj9Q3KtrAfF6YoAnsCf0ym	$2y$12$.jXzJi48Mtro2iSUHg2N5eYkw7iDC3bBfFyb2lFMNE4yt78PtNXQy	2026-07-18 00:14:27	t	f	\N	2026-07-18 00:14:27	2026-07-18 00:14:27	\N
3	Luis	Martínez	asesor1@mso.com	2026-07-18 00:14:27	$2y$12$sMGfx/zjzlFF7cMXix49lukKOAqsPSypDf02Q3rgplizV51ewSBu.	\N	+58 416 3456789	\N	Asesor inmobiliario especializado en propiedades residenciales de lujo.	Propiedades de Lujo	"{\\"whatsapp\\":\\"584163456789\\",\\"instagram\\":\\"luis.martinez.inmobiliaria\\",\\"facebook\\":\\"luismartinez.asesor\\"}"	V	34567890	1	3	3	4	4	\N	["\\u00bfCu\\u00e1l es el nombre de tu primera escuela?","\\u00bfCu\\u00e1l es el apellido de soltera de tu madre?","\\u00bfCu\\u00e1l es tu deporte favorito?"]	$2y$12$BewLbs5V0MsF3W3V4nl5auwhC8MuDnWBvcl/PW7anE.SD8/mNFz16	$2y$12$0eL5.gT1zfyzFsTpcvONtuwLai/of2peyly1g7r8SDOdabhbuRUZa	$2y$12$sSiV50kxLwDU2H9RyrOZreVB7teK21g8rs92JB1zcPFi1/S4BRHey	2026-07-18 00:14:28	t	f	\N	2026-07-18 00:14:28	2026-07-18 00:14:28	\N
4	Ana Lucía	Fernández	asesor2@mso.com	2026-07-18 00:14:29	$2y$12$9GVrayjzU.pX6Kc7248LfOvr3DTrtba18A/LYSkHWTpVlS9nGNnwS	\N	+58 412 9876543	\N	Especialista en alquileres comerciales y oficinas.	Inmuebles Comerciales	"{\\"whatsapp\\":\\"584129876543\\",\\"instagram\\":\\"ana.fernandez.inmuebles\\",\\"linkedin\\":\\"ana-fernandez-inmobiliaria\\"}"	V	45678901	1	1	1	2	2	\N	["\\u00bfCu\\u00e1l es el apellido de soltera de tu madre?","\\u00bfCu\\u00e1l es el modelo de tu primer auto?","\\u00bfCu\\u00e1l es tu lugar favorito para vacacionar?"]	$2y$12$vo8UYXj5zHPwIHTicLOuFuwcBjcFnC2tQU6Xlma1UStiTvTMjV0Y.	$2y$12$8A127Q2zw94GGu9cSeQSU.IsN0YM8l35/LmvzuBgpA14AkZSOMTFq	$2y$12$E2Ijm5Yl6r2yJvJSAhu2.OIWOHPm3.oYtS6zuURW677A6mRicbeD.	2026-07-18 00:14:29	t	f	\N	2026-07-18 00:14:29	2026-07-18 00:14:29	\N
5	Roberto	Sánchez	auditor@mso.com	2026-07-18 00:14:30	$2y$12$Ra2JT6w5wMy3HoKYqBetIedQ4MHFQbmteQQdLpHyyiQE3ylrgYCCa	\N	+58 414 5678901	\N	Auditor financiero especializado en el sector inmobiliario.	Auditoría y Control	\N	V	56789012	1	2	2	3	3	\N	["\\u00bfCu\\u00e1l es el nombre de tu mejor amigo de la infancia?","\\u00bfCu\\u00e1l es el nombre de tu mejor amigo?","\\u00bfCu\\u00e1l es el modelo de tu primer auto?"]	$2y$12$.FeUaYZ3k75W/agRzeE6ieLTfyHm9dGr/Z278xqulXoXwgi.q1uga	$2y$12$nU2Lg8lPVA4wuR.9a5jIt.3rb2Qa83JUfpwblF8bH91q71Fu7P9C.	$2y$12$YkmYk6ScuV4Zip.8MnIdHO1AQdjxTrCc/7dAtGoLPUNR2ruEl2c02	2026-07-18 00:14:30	t	f	\N	2026-07-18 00:14:30	2026-07-18 00:14:30	\N
6	Pedro	Pérez	pedro@test.com	2026-07-18 00:14:31	$2y$12$aN1jLcY1iIaVLyn60Cl.iu2VrCNwNuqUp2fN//ddFwshrl2DaFuTS	\N	04141234567	\N	\N	\N	\N	V	15975346	1	1	1	1	1	\N	["\\u00bfCu\\u00e1l es el nombre de tu hijo\\/a?","\\u00bfCu\\u00e1l es el apellido de soltera de tu madre?","\\u00bfCu\\u00e1l es el nombre de tu mejor amigo?"]	$2y$12$..g29riZKWU86TsVzBhURegJpgsbt7HXX3mfT1x9EdiKVtRAGpLgy	$2y$12$UHUf2w.s5YGgfcgnOebQBOjDi99piNuSawOeUUM73SrIETCJHKM7.	$2y$12$VEwfjVzmTo2i5jnfAjpBau0x8KZoCKqWmEV.DemffIGfV8CTbuMZC	2026-07-18 00:14:31	t	f	\N	2026-07-18 00:14:31	2026-07-18 00:14:31	\N
7	María	Rodríguez	maria.cliente@test.com	2026-07-18 00:14:32	$2y$12$bu0FNBXHLVfmeqSDT38G.ukXm0AUAmDr98oc/FybS6YaLYSCoAW4e	\N	04241234567	\N	\N	\N	\N	V	26789456	1	3	3	4	4	\N	["\\u00bfCu\\u00e1l es el nombre de tu padre?","\\u00bfCu\\u00e1l es el nombre de tu abuelo favorito?","\\u00bfCu\\u00e1l es el nombre de tu primera mascota?"]	$2y$12$meTgb2DlAf.yFEMnBvicjuxK5VSxoMyb.acbh00Q7SDhKo6ulMNwi	$2y$12$3EjotQZpApuf6dqeK0VNze/uI7MiGfsKdo0QSNuKxuDxCR9m3NXFC	$2y$12$tnUuzeBUIQGYDRMaYe8XX.FXOAFWMWEKSoiel6euL7.20TH/MrTXm	2026-07-18 00:14:33	t	f	\N	2026-07-18 00:14:33	2026-07-18 00:14:33	\N
\.


--
-- TOC entry 5504 (class 0 OID 0)
-- Dependencies: 273
-- Name: account_reactivation_tokens_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.account_reactivation_tokens_id_seq', 1, false);


--
-- TOC entry 5505 (class 0 OID 0)
-- Dependencies: 279
-- Name: appointment_settings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.appointment_settings_id_seq', 1, true);


--
-- TOC entry 5506 (class 0 OID 0)
-- Dependencies: 248
-- Name: appointments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.appointments_id_seq', 1, false);


--
-- TOC entry 5507 (class 0 OID 0)
-- Dependencies: 260
-- Name: audit_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.audit_logs_id_seq', 5, true);


--
-- TOC entry 5508 (class 0 OID 0)
-- Dependencies: 242
-- Name: categories_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.categories_id_seq', 1, true);


--
-- TOC entry 5509 (class 0 OID 0)
-- Dependencies: 240
-- Name: cities_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.cities_id_seq', 4, true);


--
-- TOC entry 5510 (class 0 OID 0)
-- Dependencies: 254
-- Name: conversations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.conversations_id_seq', 1, false);


--
-- TOC entry 5511 (class 0 OID 0)
-- Dependencies: 232
-- Name: countries_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.countries_id_seq', 1, true);


--
-- TOC entry 5512 (class 0 OID 0)
-- Dependencies: 230
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- TOC entry 5513 (class 0 OID 0)
-- Dependencies: 250
-- Name: favorites_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.favorites_id_seq', 1, true);


--
-- TOC entry 5514 (class 0 OID 0)
-- Dependencies: 227
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- TOC entry 5515 (class 0 OID 0)
-- Dependencies: 252
-- Name: leads_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.leads_id_seq', 1, false);


--
-- TOC entry 5516 (class 0 OID 0)
-- Dependencies: 256
-- Name: messages_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.messages_id_seq', 1, false);


--
-- TOC entry 5517 (class 0 OID 0)
-- Dependencies: 219
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 32, true);


--
-- TOC entry 5518 (class 0 OID 0)
-- Dependencies: 236
-- Name: municipalities_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.municipalities_id_seq', 3, true);


--
-- TOC entry 5519 (class 0 OID 0)
-- Dependencies: 238
-- Name: parishes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.parishes_id_seq', 4, true);


--
-- TOC entry 5520 (class 0 OID 0)
-- Dependencies: 266
-- Name: permissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.permissions_id_seq', 51, true);


--
-- TOC entry 5521 (class 0 OID 0)
-- Dependencies: 264
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.personal_access_tokens_id_seq', 1, false);


--
-- TOC entry 5522 (class 0 OID 0)
-- Dependencies: 244
-- Name: properties_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.properties_id_seq', 1, true);


--
-- TOC entry 5523 (class 0 OID 0)
-- Dependencies: 246
-- Name: property_images_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.property_images_id_seq', 3, true);


--
-- TOC entry 5524 (class 0 OID 0)
-- Dependencies: 275
-- Name: report_snapshots_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.report_snapshots_id_seq', 1, false);


--
-- TOC entry 5525 (class 0 OID 0)
-- Dependencies: 268
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.roles_id_seq', 5, true);


--
-- TOC entry 5526 (class 0 OID 0)
-- Dependencies: 283
-- Name: service_galleries_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.service_galleries_id_seq', 1, false);


--
-- TOC entry 5527 (class 0 OID 0)
-- Dependencies: 281
-- Name: services_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.services_id_seq', 1, false);


--
-- TOC entry 5528 (class 0 OID 0)
-- Dependencies: 258
-- Name: settings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.settings_id_seq', 1, false);


--
-- TOC entry 5529 (class 0 OID 0)
-- Dependencies: 277
-- Name: site_configurations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.site_configurations_id_seq', 1, true);


--
-- TOC entry 5530 (class 0 OID 0)
-- Dependencies: 234
-- Name: states_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.states_id_seq', 3, true);


--
-- TOC entry 5531 (class 0 OID 0)
-- Dependencies: 262
-- Name: user_notifications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.user_notifications_id_seq', 3, true);


--
-- TOC entry 5532 (class 0 OID 0)
-- Dependencies: 221
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 7, true);


--
-- TOC entry 5193 (class 2606 OID 140770)
-- Name: account_reactivation_tokens account_reactivation_tokens_email_token_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.account_reactivation_tokens
    ADD CONSTRAINT account_reactivation_tokens_email_token_unique UNIQUE (email, token);


--
-- TOC entry 5195 (class 2606 OID 140768)
-- Name: account_reactivation_tokens account_reactivation_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.account_reactivation_tokens
    ADD CONSTRAINT account_reactivation_tokens_pkey PRIMARY KEY (id);


--
-- TOC entry 5205 (class 2606 OID 140847)
-- Name: appointment_settings appointment_settings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.appointment_settings
    ADD CONSTRAINT appointment_settings_pkey PRIMARY KEY (id);


--
-- TOC entry 5105 (class 2606 OID 140445)
-- Name: appointments appointments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.appointments
    ADD CONSTRAINT appointments_pkey PRIMARY KEY (id);


--
-- TOC entry 5164 (class 2606 OID 140639)
-- Name: audit_logs audit_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT audit_logs_pkey PRIMARY KEY (id);


--
-- TOC entry 5043 (class 2606 OID 140174)
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- TOC entry 5040 (class 2606 OID 140163)
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- TOC entry 5064 (class 2606 OID 140309)
-- Name: categories categories_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categories
    ADD CONSTRAINT categories_pkey PRIMARY KEY (id);


--
-- TOC entry 5066 (class 2606 OID 140311)
-- Name: categories categories_slug_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categories
    ADD CONSTRAINT categories_slug_unique UNIQUE (slug);


--
-- TOC entry 5062 (class 2606 OID 140288)
-- Name: cities cities_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cities
    ADD CONSTRAINT cities_pkey PRIMARY KEY (id);


--
-- TOC entry 5138 (class 2606 OID 140571)
-- Name: conversations conversations_client_id_asesor_id_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT conversations_client_id_asesor_id_unique UNIQUE (client_id, asesor_id);


--
-- TOC entry 5145 (class 2606 OID 140559)
-- Name: conversations conversations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT conversations_pkey PRIMARY KEY (id);


--
-- TOC entry 5054 (class 2606 OID 140233)
-- Name: countries countries_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.countries
    ADD CONSTRAINT countries_pkey PRIMARY KEY (id);


--
-- TOC entry 5050 (class 2606 OID 140222)
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- TOC entry 5052 (class 2606 OID 140224)
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- TOC entry 5114 (class 2606 OID 140481)
-- Name: favorites favorites_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.favorites
    ADD CONSTRAINT favorites_pkey PRIMARY KEY (id);


--
-- TOC entry 5120 (class 2606 OID 140493)
-- Name: favorites favorites_user_id_property_id_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.favorites
    ADD CONSTRAINT favorites_user_id_property_id_unique UNIQUE (user_id, property_id);


--
-- TOC entry 5211 (class 2606 OID 140858)
-- Name: appointment_settings idx_appointment_settings_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.appointment_settings
    ADD CONSTRAINT idx_appointment_settings_user_id_unique UNIQUE (user_id);


--
-- TOC entry 5048 (class 2606 OID 140205)
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- TOC entry 5045 (class 2606 OID 140190)
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- TOC entry 5129 (class 2606 OID 140520)
-- Name: leads leads_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leads
    ADD CONSTRAINT leads_pkey PRIMARY KEY (id);


--
-- TOC entry 5152 (class 2606 OID 140593)
-- Name: messages messages_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.messages
    ADD CONSTRAINT messages_pkey PRIMARY KEY (id);


--
-- TOC entry 5010 (class 2606 OID 140096)
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- TOC entry 5185 (class 2606 OID 140727)
-- Name: model_has_permissions model_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_pkey PRIMARY KEY (permission_id, model_id, model_type);


--
-- TOC entry 5188 (class 2606 OID 140741)
-- Name: model_has_roles model_has_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_pkey PRIMARY KEY (role_id, model_id, model_type);


--
-- TOC entry 5058 (class 2606 OID 140258)
-- Name: municipalities municipalities_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.municipalities
    ADD CONSTRAINT municipalities_pkey PRIMARY KEY (id);


--
-- TOC entry 5060 (class 2606 OID 140273)
-- Name: parishes parishes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.parishes
    ADD CONSTRAINT parishes_pkey PRIMARY KEY (id);


--
-- TOC entry 5033 (class 2606 OID 140141)
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- TOC entry 5176 (class 2606 OID 140699)
-- Name: permissions permissions_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_name_guard_name_unique UNIQUE (name, guard_name);


--
-- TOC entry 5178 (class 2606 OID 140697)
-- Name: permissions permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (id);


--
-- TOC entry 5171 (class 2606 OID 140681)
-- Name: personal_access_tokens personal_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_pkey PRIMARY KEY (id);


--
-- TOC entry 5173 (class 2606 OID 140684)
-- Name: personal_access_tokens personal_access_tokens_token_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_token_unique UNIQUE (token);


--
-- TOC entry 5082 (class 2606 OID 140339)
-- Name: properties properties_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.properties
    ADD CONSTRAINT properties_pkey PRIMARY KEY (id);


--
-- TOC entry 5096 (class 2606 OID 140415)
-- Name: property_images property_images_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.property_images
    ADD CONSTRAINT property_images_pkey PRIMARY KEY (id);


--
-- TOC entry 5198 (class 2606 OID 140791)
-- Name: report_snapshots report_snapshots_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.report_snapshots
    ADD CONSTRAINT report_snapshots_pkey PRIMARY KEY (id);


--
-- TOC entry 5201 (class 2606 OID 140798)
-- Name: report_snapshots report_snapshots_user_id_report_type_period_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.report_snapshots
    ADD CONSTRAINT report_snapshots_user_id_report_type_period_unique UNIQUE (user_id, report_type, period);


--
-- TOC entry 5190 (class 2606 OID 140758)
-- Name: role_has_permissions role_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_pkey PRIMARY KEY (permission_id, role_id);


--
-- TOC entry 5180 (class 2606 OID 140713)
-- Name: roles roles_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_name_guard_name_unique UNIQUE (name, guard_name);


--
-- TOC entry 5182 (class 2606 OID 140711)
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- TOC entry 5221 (class 2606 OID 140900)
-- Name: service_galleries service_galleries_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.service_galleries
    ADD CONSTRAINT service_galleries_pkey PRIMARY KEY (id);


--
-- TOC entry 5217 (class 2606 OID 140882)
-- Name: services services_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.services
    ADD CONSTRAINT services_pkey PRIMARY KEY (id);


--
-- TOC entry 5219 (class 2606 OID 140884)
-- Name: services services_slug_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.services
    ADD CONSTRAINT services_slug_unique UNIQUE (slug);


--
-- TOC entry 5036 (class 2606 OID 140151)
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- TOC entry 5156 (class 2606 OID 140629)
-- Name: settings settings_key_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT settings_key_unique UNIQUE (key);


--
-- TOC entry 5158 (class 2606 OID 140627)
-- Name: settings settings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT settings_pkey PRIMARY KEY (id);


--
-- TOC entry 5203 (class 2606 OID 140824)
-- Name: site_configurations site_configurations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.site_configurations
    ADD CONSTRAINT site_configurations_pkey PRIMARY KEY (id);


--
-- TOC entry 5056 (class 2606 OID 140243)
-- Name: states states_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.states
    ADD CONSTRAINT states_pkey PRIMARY KEY (id);


--
-- TOC entry 5167 (class 2606 OID 140661)
-- Name: user_notifications user_notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_notifications
    ADD CONSTRAINT user_notifications_pkey PRIMARY KEY (id);


--
-- TOC entry 5017 (class 2606 OID 140132)
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- TOC entry 5028 (class 2606 OID 140113)
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- TOC entry 5191 (class 1259 OID 140771)
-- Name: account_reactivation_tokens_email_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX account_reactivation_tokens_email_index ON public.account_reactivation_tokens USING btree (email);


--
-- TOC entry 5100 (class 1259 OID 140463)
-- Name: appointments_asesor_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX appointments_asesor_id_index ON public.appointments USING btree (asesor_id);


--
-- TOC entry 5101 (class 1259 OID 140467)
-- Name: appointments_asesor_id_scheduled_date_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX appointments_asesor_id_scheduled_date_index ON public.appointments USING btree (asesor_id, scheduled_date);


--
-- TOC entry 5102 (class 1259 OID 140471)
-- Name: appointments_asesor_id_status_scheduled_date_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX appointments_asesor_id_status_scheduled_date_index ON public.appointments USING btree (asesor_id, status, scheduled_date);


--
-- TOC entry 5103 (class 1259 OID 140466)
-- Name: appointments_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX appointments_created_at_index ON public.appointments USING btree (created_at);


--
-- TOC entry 5106 (class 1259 OID 140462)
-- Name: appointments_property_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX appointments_property_id_index ON public.appointments USING btree (property_id);


--
-- TOC entry 5107 (class 1259 OID 140470)
-- Name: appointments_property_id_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX appointments_property_id_status_index ON public.appointments USING btree (property_id, status);


--
-- TOC entry 5108 (class 1259 OID 140465)
-- Name: appointments_scheduled_date_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX appointments_scheduled_date_index ON public.appointments USING btree (scheduled_date);


--
-- TOC entry 5109 (class 1259 OID 140464)
-- Name: appointments_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX appointments_status_index ON public.appointments USING btree (status);


--
-- TOC entry 5110 (class 1259 OID 140468)
-- Name: appointments_status_scheduled_date_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX appointments_status_scheduled_date_index ON public.appointments USING btree (status, scheduled_date);


--
-- TOC entry 5111 (class 1259 OID 140461)
-- Name: appointments_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX appointments_user_id_index ON public.appointments USING btree (user_id);


--
-- TOC entry 5112 (class 1259 OID 140469)
-- Name: appointments_user_id_scheduled_date_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX appointments_user_id_scheduled_date_index ON public.appointments USING btree (user_id, scheduled_date);


--
-- TOC entry 5159 (class 1259 OID 140801)
-- Name: audit_logs_action_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX audit_logs_action_index ON public.audit_logs USING btree (action);


--
-- TOC entry 5160 (class 1259 OID 140906)
-- Name: audit_logs_auditable_type_auditable_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX audit_logs_auditable_type_auditable_id_index ON public.audit_logs USING btree (auditable_type, auditable_id);


--
-- TOC entry 5161 (class 1259 OID 140646)
-- Name: audit_logs_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX audit_logs_created_at_index ON public.audit_logs USING btree (created_at);


--
-- TOC entry 5162 (class 1259 OID 140802)
-- Name: audit_logs_event_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX audit_logs_event_index ON public.audit_logs USING btree (event);


--
-- TOC entry 5165 (class 1259 OID 140645)
-- Name: audit_logs_subject_type_subject_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX audit_logs_subject_type_subject_id_index ON public.audit_logs USING btree (subject_type, subject_id);


--
-- TOC entry 5038 (class 1259 OID 140164)
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- TOC entry 5041 (class 1259 OID 140175)
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- TOC entry 5135 (class 1259 OID 140573)
-- Name: conversations_asesor_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX conversations_asesor_id_index ON public.conversations USING btree (asesor_id);


--
-- TOC entry 5136 (class 1259 OID 140577)
-- Name: conversations_asesor_id_is_active_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX conversations_asesor_id_is_active_index ON public.conversations USING btree (asesor_id, is_active);


--
-- TOC entry 5139 (class 1259 OID 140572)
-- Name: conversations_client_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX conversations_client_id_index ON public.conversations USING btree (client_id);


--
-- TOC entry 5140 (class 1259 OID 140576)
-- Name: conversations_client_id_is_active_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX conversations_client_id_is_active_index ON public.conversations USING btree (client_id, is_active);


--
-- TOC entry 5141 (class 1259 OID 140574)
-- Name: conversations_is_active_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX conversations_is_active_index ON public.conversations USING btree (is_active);


--
-- TOC entry 5142 (class 1259 OID 140575)
-- Name: conversations_last_message_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX conversations_last_message_at_index ON public.conversations USING btree (last_message_at);


--
-- TOC entry 5143 (class 1259 OID 140578)
-- Name: conversations_last_message_at_is_active_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX conversations_last_message_at_is_active_index ON public.conversations USING btree (last_message_at, is_active);


--
-- TOC entry 5115 (class 1259 OID 140497)
-- Name: favorites_property_id_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX favorites_property_id_created_at_index ON public.favorites USING btree (property_id, created_at);


--
-- TOC entry 5116 (class 1259 OID 140495)
-- Name: favorites_property_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX favorites_property_id_index ON public.favorites USING btree (property_id);


--
-- TOC entry 5117 (class 1259 OID 140496)
-- Name: favorites_user_id_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX favorites_user_id_created_at_index ON public.favorites USING btree (user_id, created_at);


--
-- TOC entry 5118 (class 1259 OID 140494)
-- Name: favorites_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX favorites_user_id_index ON public.favorites USING btree (user_id);


--
-- TOC entry 5206 (class 1259 OID 140854)
-- Name: idx_appointment_settings_active_always; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_appointment_settings_active_always ON public.appointment_settings USING btree (is_active, apply_always);


--
-- TOC entry 5207 (class 1259 OID 140862)
-- Name: idx_appointment_settings_apply_always; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_appointment_settings_apply_always ON public.appointment_settings USING btree (apply_always);


--
-- TOC entry 5208 (class 1259 OID 140859)
-- Name: idx_appointment_settings_is_active; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_appointment_settings_is_active ON public.appointment_settings USING btree (is_active);


--
-- TOC entry 5209 (class 1259 OID 140853)
-- Name: idx_appointment_settings_user_active; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_appointment_settings_user_active ON public.appointment_settings USING btree (user_id, is_active);


--
-- TOC entry 5212 (class 1259 OID 140856)
-- Name: idx_appointment_settings_user_valid; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_appointment_settings_user_valid ON public.appointment_settings USING btree (user_id, valid_from, valid_to);


--
-- TOC entry 5213 (class 1259 OID 140860)
-- Name: idx_appointment_settings_valid_from; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_appointment_settings_valid_from ON public.appointment_settings USING btree (valid_from);


--
-- TOC entry 5214 (class 1259 OID 140855)
-- Name: idx_appointment_settings_valid_range; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_appointment_settings_valid_range ON public.appointment_settings USING btree (valid_from, valid_to);


--
-- TOC entry 5215 (class 1259 OID 140861)
-- Name: idx_appointment_settings_valid_to; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_appointment_settings_valid_to ON public.appointment_settings USING btree (valid_to);


--
-- TOC entry 5046 (class 1259 OID 140191)
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- TOC entry 5121 (class 1259 OID 140546)
-- Name: leads_asesor_id_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX leads_asesor_id_created_at_index ON public.leads USING btree (asesor_id, created_at);


--
-- TOC entry 5122 (class 1259 OID 140539)
-- Name: leads_asesor_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX leads_asesor_id_index ON public.leads USING btree (asesor_id);


--
-- TOC entry 5123 (class 1259 OID 140542)
-- Name: leads_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX leads_created_at_index ON public.leads USING btree (created_at);


--
-- TOC entry 5124 (class 1259 OID 140536)
-- Name: leads_email_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX leads_email_index ON public.leads USING btree (email);


--
-- TOC entry 5125 (class 1259 OID 140544)
-- Name: leads_email_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX leads_email_status_index ON public.leads USING btree (email, status);


--
-- TOC entry 5126 (class 1259 OID 140537)
-- Name: leads_phone_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX leads_phone_index ON public.leads USING btree (phone);


--
-- TOC entry 5127 (class 1259 OID 140545)
-- Name: leads_phone_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX leads_phone_status_index ON public.leads USING btree (phone, status);


--
-- TOC entry 5130 (class 1259 OID 140541)
-- Name: leads_property_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX leads_property_id_index ON public.leads USING btree (property_id);


--
-- TOC entry 5131 (class 1259 OID 140543)
-- Name: leads_status_asesor_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX leads_status_asesor_id_index ON public.leads USING btree (status, asesor_id);


--
-- TOC entry 5132 (class 1259 OID 140547)
-- Name: leads_status_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX leads_status_created_at_index ON public.leads USING btree (status, created_at);


--
-- TOC entry 5133 (class 1259 OID 140538)
-- Name: leads_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX leads_status_index ON public.leads USING btree (status);


--
-- TOC entry 5134 (class 1259 OID 140540)
-- Name: leads_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX leads_user_id_index ON public.leads USING btree (user_id);


--
-- TOC entry 5146 (class 1259 OID 140610)
-- Name: messages_conversation_id_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX messages_conversation_id_created_at_index ON public.messages USING btree (conversation_id, created_at);


--
-- TOC entry 5147 (class 1259 OID 140604)
-- Name: messages_conversation_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX messages_conversation_id_index ON public.messages USING btree (conversation_id);


--
-- TOC entry 5148 (class 1259 OID 140608)
-- Name: messages_conversation_id_is_read_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX messages_conversation_id_is_read_index ON public.messages USING btree (conversation_id, is_read);


--
-- TOC entry 5149 (class 1259 OID 140607)
-- Name: messages_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX messages_created_at_index ON public.messages USING btree (created_at);


--
-- TOC entry 5150 (class 1259 OID 140606)
-- Name: messages_is_read_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX messages_is_read_index ON public.messages USING btree (is_read);


--
-- TOC entry 5153 (class 1259 OID 140605)
-- Name: messages_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX messages_user_id_index ON public.messages USING btree (user_id);


--
-- TOC entry 5154 (class 1259 OID 140609)
-- Name: messages_user_id_is_read_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX messages_user_id_is_read_index ON public.messages USING btree (user_id, is_read);


--
-- TOC entry 5183 (class 1259 OID 140720)
-- Name: model_has_permissions_model_id_model_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX model_has_permissions_model_id_model_type_index ON public.model_has_permissions USING btree (model_id, model_type);


--
-- TOC entry 5186 (class 1259 OID 140734)
-- Name: model_has_roles_model_id_model_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX model_has_roles_model_id_model_type_index ON public.model_has_roles USING btree (model_id, model_type);


--
-- TOC entry 5169 (class 1259 OID 140685)
-- Name: personal_access_tokens_expires_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX personal_access_tokens_expires_at_index ON public.personal_access_tokens USING btree (expires_at);


--
-- TOC entry 5174 (class 1259 OID 140682)
-- Name: personal_access_tokens_tokenable_type_tokenable_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON public.personal_access_tokens USING btree (tokenable_type, tokenable_id);


--
-- TOC entry 5067 (class 1259 OID 140399)
-- Name: properties_address_location_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_address_location_index ON public.properties USING btree (address, location);


--
-- TOC entry 5068 (class 1259 OID 140376)
-- Name: properties_category_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_category_id_index ON public.properties USING btree (category_id);


--
-- TOC entry 5069 (class 1259 OID 140395)
-- Name: properties_category_id_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_category_id_status_index ON public.properties USING btree (category_id, status);


--
-- TOC entry 5070 (class 1259 OID 140387)
-- Name: properties_city_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_city_id_index ON public.properties USING btree (city_id);


--
-- TOC entry 5071 (class 1259 OID 140396)
-- Name: properties_city_id_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_city_id_status_index ON public.properties USING btree (city_id, status);


--
-- TOC entry 5072 (class 1259 OID 140385)
-- Name: properties_country_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_country_id_index ON public.properties USING btree (country_id);


--
-- TOC entry 5073 (class 1259 OID 140390)
-- Name: properties_country_id_state_id_municipality_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_country_id_state_id_municipality_id_index ON public.properties USING btree (country_id, state_id, municipality_id);


--
-- TOC entry 5074 (class 1259 OID 140383)
-- Name: properties_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_created_at_index ON public.properties USING btree (created_at);


--
-- TOC entry 5075 (class 1259 OID 140392)
-- Name: properties_created_at_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_created_at_status_index ON public.properties USING btree (created_at, status);


--
-- TOC entry 5076 (class 1259 OID 140384)
-- Name: properties_deleted_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_deleted_at_index ON public.properties USING btree (deleted_at);


--
-- TOC entry 5077 (class 1259 OID 140382)
-- Name: properties_featured_until_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_featured_until_index ON public.properties USING btree (featured_until);


--
-- TOC entry 5078 (class 1259 OID 140391)
-- Name: properties_is_featured_featured_until_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_is_featured_featured_until_index ON public.properties USING btree (is_featured, featured_until);


--
-- TOC entry 5079 (class 1259 OID 140381)
-- Name: properties_is_featured_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_is_featured_index ON public.properties USING btree (is_featured);


--
-- TOC entry 5080 (class 1259 OID 140388)
-- Name: properties_municipality_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_municipality_id_index ON public.properties USING btree (municipality_id);


--
-- TOC entry 5083 (class 1259 OID 140377)
-- Name: properties_price_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_price_index ON public.properties USING btree (price);


--
-- TOC entry 5084 (class 1259 OID 140386)
-- Name: properties_state_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_state_id_index ON public.properties USING btree (state_id);


--
-- TOC entry 5085 (class 1259 OID 140397)
-- Name: properties_state_id_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_state_id_status_index ON public.properties USING btree (state_id, status);


--
-- TOC entry 5086 (class 1259 OID 140378)
-- Name: properties_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_status_index ON public.properties USING btree (status);


--
-- TOC entry 5087 (class 1259 OID 140393)
-- Name: properties_status_type_price_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_status_type_price_created_at_index ON public.properties USING btree (status, type, price, created_at);


--
-- TOC entry 5088 (class 1259 OID 140389)
-- Name: properties_status_type_price_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_status_type_price_index ON public.properties USING btree (status, type, price);


--
-- TOC entry 5089 (class 1259 OID 140398)
-- Name: properties_title_description_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_title_description_index ON public.properties USING btree (title, description);


--
-- TOC entry 5090 (class 1259 OID 140379)
-- Name: properties_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_type_index ON public.properties USING btree (type);


--
-- TOC entry 5091 (class 1259 OID 140375)
-- Name: properties_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_user_id_index ON public.properties USING btree (user_id);


--
-- TOC entry 5092 (class 1259 OID 140394)
-- Name: properties_user_id_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_user_id_status_index ON public.properties USING btree (user_id, status);


--
-- TOC entry 5093 (class 1259 OID 140380)
-- Name: properties_views_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_views_index ON public.properties USING btree (views);


--
-- TOC entry 5094 (class 1259 OID 140422)
-- Name: property_images_is_primary_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX property_images_is_primary_index ON public.property_images USING btree (is_primary);


--
-- TOC entry 5097 (class 1259 OID 140421)
-- Name: property_images_property_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX property_images_property_id_index ON public.property_images USING btree (property_id);


--
-- TOC entry 5098 (class 1259 OID 140423)
-- Name: property_images_property_id_is_primary_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX property_images_property_id_is_primary_index ON public.property_images USING btree (property_id, is_primary);


--
-- TOC entry 5099 (class 1259 OID 140424)
-- Name: property_images_property_id_order_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX property_images_property_id_order_index ON public.property_images USING btree (property_id, "order");


--
-- TOC entry 5196 (class 1259 OID 140800)
-- Name: report_snapshots_period_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX report_snapshots_period_index ON public.report_snapshots USING btree (period);


--
-- TOC entry 5199 (class 1259 OID 140799)
-- Name: report_snapshots_report_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX report_snapshots_report_type_index ON public.report_snapshots USING btree (report_type);


--
-- TOC entry 5034 (class 1259 OID 140153)
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- TOC entry 5037 (class 1259 OID 140152)
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- TOC entry 5168 (class 1259 OID 140667)
-- Name: user_notifications_user_id_read_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX user_notifications_user_id_read_at_index ON public.user_notifications USING btree (user_id, read_at);


--
-- TOC entry 5011 (class 1259 OID 140126)
-- Name: users_country_id_state_id_city_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_country_id_state_id_city_id_index ON public.users USING btree (country_id, state_id, city_id);


--
-- TOC entry 5012 (class 1259 OID 140119)
-- Name: users_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_created_at_index ON public.users USING btree (created_at);


--
-- TOC entry 5013 (class 1259 OID 140120)
-- Name: users_deleted_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_deleted_at_index ON public.users USING btree (deleted_at);


--
-- TOC entry 5014 (class 1259 OID 140114)
-- Name: users_email_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_email_index ON public.users USING btree (email);


--
-- TOC entry 5015 (class 1259 OID 140129)
-- Name: users_email_is_active_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_email_is_active_index ON public.users USING btree (email, is_active);


--
-- TOC entry 5018 (class 1259 OID 140121)
-- Name: users_id_number_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_id_number_index ON public.users USING btree (id_number);


--
-- TOC entry 5019 (class 1259 OID 140128)
-- Name: users_id_type_id_number_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_id_type_id_number_index ON public.users USING btree (id_type, id_number);


--
-- TOC entry 5020 (class 1259 OID 140127)
-- Name: users_is_active_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_is_active_created_at_index ON public.users USING btree (is_active, created_at);


--
-- TOC entry 5021 (class 1259 OID 140116)
-- Name: users_is_active_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_is_active_index ON public.users USING btree (is_active);


--
-- TOC entry 5022 (class 1259 OID 140117)
-- Name: users_is_online_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_is_online_index ON public.users USING btree (is_online);


--
-- TOC entry 5023 (class 1259 OID 140118)
-- Name: users_last_seen_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_last_seen_at_index ON public.users USING btree (last_seen_at);


--
-- TOC entry 5024 (class 1259 OID 140125)
-- Name: users_name_last_name_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_name_last_name_index ON public.users USING btree (name, last_name);


--
-- TOC entry 5025 (class 1259 OID 140115)
-- Name: users_phone_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_phone_index ON public.users USING btree (phone);


--
-- TOC entry 5026 (class 1259 OID 140130)
-- Name: users_phone_is_active_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_phone_is_active_index ON public.users USING btree (phone, is_active);


--
-- TOC entry 5029 (class 1259 OID 140122)
-- Name: users_security_answer_1_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_security_answer_1_index ON public.users USING btree (security_answer_1);


--
-- TOC entry 5030 (class 1259 OID 140123)
-- Name: users_security_answer_2_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_security_answer_2_index ON public.users USING btree (security_answer_2);


--
-- TOC entry 5031 (class 1259 OID 140124)
-- Name: users_security_answer_3_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_security_answer_3_index ON public.users USING btree (security_answer_3);


--
-- TOC entry 5234 (class 2606 OID 140456)
-- Name: appointments appointments_asesor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.appointments
    ADD CONSTRAINT appointments_asesor_id_foreign FOREIGN KEY (asesor_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5235 (class 2606 OID 140451)
-- Name: appointments appointments_property_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.appointments
    ADD CONSTRAINT appointments_property_id_foreign FOREIGN KEY (property_id) REFERENCES public.properties(id) ON DELETE CASCADE;


--
-- TOC entry 5236 (class 2606 OID 140446)
-- Name: appointments appointments_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.appointments
    ADD CONSTRAINT appointments_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5246 (class 2606 OID 140640)
-- Name: audit_logs audit_logs_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT audit_logs_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 5225 (class 2606 OID 140289)
-- Name: cities cities_parish_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cities
    ADD CONSTRAINT cities_parish_id_foreign FOREIGN KEY (parish_id) REFERENCES public.parishes(id) ON DELETE CASCADE;


--
-- TOC entry 5242 (class 2606 OID 140565)
-- Name: conversations conversations_asesor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT conversations_asesor_id_foreign FOREIGN KEY (asesor_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5243 (class 2606 OID 140560)
-- Name: conversations conversations_client_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT conversations_client_id_foreign FOREIGN KEY (client_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5237 (class 2606 OID 140487)
-- Name: favorites favorites_property_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.favorites
    ADD CONSTRAINT favorites_property_id_foreign FOREIGN KEY (property_id) REFERENCES public.properties(id) ON DELETE CASCADE;


--
-- TOC entry 5238 (class 2606 OID 140482)
-- Name: favorites favorites_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.favorites
    ADD CONSTRAINT favorites_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5253 (class 2606 OID 140848)
-- Name: appointment_settings idx_appointment_settings_user_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.appointment_settings
    ADD CONSTRAINT idx_appointment_settings_user_id FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5239 (class 2606 OID 140531)
-- Name: leads leads_asesor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leads
    ADD CONSTRAINT leads_asesor_id_foreign FOREIGN KEY (asesor_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 5240 (class 2606 OID 140526)
-- Name: leads leads_property_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leads
    ADD CONSTRAINT leads_property_id_foreign FOREIGN KEY (property_id) REFERENCES public.properties(id) ON DELETE SET NULL;


--
-- TOC entry 5241 (class 2606 OID 140521)
-- Name: leads leads_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leads
    ADD CONSTRAINT leads_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 5244 (class 2606 OID 140594)
-- Name: messages messages_conversation_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.messages
    ADD CONSTRAINT messages_conversation_id_foreign FOREIGN KEY (conversation_id) REFERENCES public.conversations(id) ON DELETE CASCADE;


--
-- TOC entry 5245 (class 2606 OID 140599)
-- Name: messages messages_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.messages
    ADD CONSTRAINT messages_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5248 (class 2606 OID 140721)
-- Name: model_has_permissions model_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- TOC entry 5249 (class 2606 OID 140735)
-- Name: model_has_roles model_has_roles_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- TOC entry 5223 (class 2606 OID 140259)
-- Name: municipalities municipalities_state_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.municipalities
    ADD CONSTRAINT municipalities_state_id_foreign FOREIGN KEY (state_id) REFERENCES public.states(id) ON DELETE CASCADE;


--
-- TOC entry 5224 (class 2606 OID 140274)
-- Name: parishes parishes_municipality_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.parishes
    ADD CONSTRAINT parishes_municipality_id_foreign FOREIGN KEY (municipality_id) REFERENCES public.municipalities(id) ON DELETE CASCADE;


--
-- TOC entry 5226 (class 2606 OID 140370)
-- Name: properties properties_category_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.properties
    ADD CONSTRAINT properties_category_id_foreign FOREIGN KEY (category_id) REFERENCES public.categories(id) ON DELETE SET NULL;


--
-- TOC entry 5227 (class 2606 OID 140360)
-- Name: properties properties_city_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.properties
    ADD CONSTRAINT properties_city_id_foreign FOREIGN KEY (city_id) REFERENCES public.cities(id) ON DELETE CASCADE;


--
-- TOC entry 5228 (class 2606 OID 140340)
-- Name: properties properties_country_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.properties
    ADD CONSTRAINT properties_country_id_foreign FOREIGN KEY (country_id) REFERENCES public.countries(id) ON DELETE CASCADE;


--
-- TOC entry 5229 (class 2606 OID 140350)
-- Name: properties properties_municipality_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.properties
    ADD CONSTRAINT properties_municipality_id_foreign FOREIGN KEY (municipality_id) REFERENCES public.municipalities(id) ON DELETE CASCADE;


--
-- TOC entry 5230 (class 2606 OID 140355)
-- Name: properties properties_parish_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.properties
    ADD CONSTRAINT properties_parish_id_foreign FOREIGN KEY (parish_id) REFERENCES public.parishes(id) ON DELETE CASCADE;


--
-- TOC entry 5231 (class 2606 OID 140345)
-- Name: properties properties_state_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.properties
    ADD CONSTRAINT properties_state_id_foreign FOREIGN KEY (state_id) REFERENCES public.states(id) ON DELETE CASCADE;


--
-- TOC entry 5232 (class 2606 OID 140365)
-- Name: properties properties_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.properties
    ADD CONSTRAINT properties_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5233 (class 2606 OID 140416)
-- Name: property_images property_images_property_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.property_images
    ADD CONSTRAINT property_images_property_id_foreign FOREIGN KEY (property_id) REFERENCES public.properties(id) ON DELETE CASCADE;


--
-- TOC entry 5252 (class 2606 OID 140792)
-- Name: report_snapshots report_snapshots_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.report_snapshots
    ADD CONSTRAINT report_snapshots_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 5250 (class 2606 OID 140747)
-- Name: role_has_permissions role_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- TOC entry 5251 (class 2606 OID 140752)
-- Name: role_has_permissions role_has_permissions_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- TOC entry 5254 (class 2606 OID 140901)
-- Name: service_galleries service_galleries_service_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.service_galleries
    ADD CONSTRAINT service_galleries_service_id_foreign FOREIGN KEY (service_id) REFERENCES public.services(id) ON DELETE CASCADE;


--
-- TOC entry 5222 (class 2606 OID 140244)
-- Name: states states_country_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.states
    ADD CONSTRAINT states_country_id_foreign FOREIGN KEY (country_id) REFERENCES public.countries(id) ON DELETE CASCADE;


--
-- TOC entry 5247 (class 2606 OID 140662)
-- Name: user_notifications user_notifications_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_notifications
    ADD CONSTRAINT user_notifications_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


-- Completed on 2026-07-17 21:29:43

--
-- PostgreSQL database dump complete
--

\unrestrict nsJfhcC09IgLgDaHXVWGAdhFx07p79aoMfIC2BOUKP4TK3fQX2uNHHgnv2rOkgS

