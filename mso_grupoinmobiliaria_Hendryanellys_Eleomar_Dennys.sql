--
-- PostgreSQL database dump
--

\restrict 4UfLCRgY9cewlrr1C2P0nocKmm5eblGMkp8EwIBCjqR73R5qPxOTVW6D9pqftMf

-- Dumped from database version 18.3
-- Dumped by pg_dump version 18.3

-- Started on 2026-07-19 00:18:58

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
-- TOC entry 5472 (class 0 OID 0)
-- Dependencies: 4
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: pg_database_owner
--

COMMENT ON SCHEMA public IS 'standard public schema';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 274 (class 1259 OID 150586)
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
-- TOC entry 273 (class 1259 OID 150585)
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
-- TOC entry 5473 (class 0 OID 0)
-- Dependencies: 273
-- Name: account_reactivation_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.account_reactivation_tokens_id_seq OWNED BY public.account_reactivation_tokens.id;


--
-- TOC entry 280 (class 1259 OID 150652)
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
-- TOC entry 279 (class 1259 OID 150651)
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
-- TOC entry 5474 (class 0 OID 0)
-- Dependencies: 279
-- Name: appointment_settings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.appointment_settings_id_seq OWNED BY public.appointment_settings.id;


--
-- TOC entry 249 (class 1259 OID 150252)
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
-- TOC entry 248 (class 1259 OID 150251)
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
-- TOC entry 5475 (class 0 OID 0)
-- Dependencies: 248
-- Name: appointments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.appointments_id_seq OWNED BY public.appointments.id;


--
-- TOC entry 261 (class 1259 OID 150457)
-- Name: audit_logs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.audit_logs (
    id bigint NOT NULL,
    user_id bigint,
    subject_type text,
    subject_id bigint,
    old_values jsonb,
    new_values jsonb,
    description text,
    ip_address character varying(45),
    user_agent text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    action text,
    event text,
    auditable_type text,
    auditable_id bigint,
    tags text,
    url text,
    user_type text
);


ALTER TABLE public.audit_logs OWNER TO postgres;

--
-- TOC entry 260 (class 1259 OID 150456)
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
-- TOC entry 5476 (class 0 OID 0)
-- Dependencies: 260
-- Name: audit_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.audit_logs_id_seq OWNED BY public.audit_logs.id;


--
-- TOC entry 225 (class 1259 OID 149980)
-- Name: cache; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO postgres;

--
-- TOC entry 226 (class 1259 OID 149991)
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO postgres;

--
-- TOC entry 243 (class 1259 OID 150121)
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
-- TOC entry 242 (class 1259 OID 150120)
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
-- TOC entry 5477 (class 0 OID 0)
-- Dependencies: 242
-- Name: categories_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.categories_id_seq OWNED BY public.categories.id;


--
-- TOC entry 241 (class 1259 OID 150106)
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
-- TOC entry 240 (class 1259 OID 150105)
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
-- TOC entry 5478 (class 0 OID 0)
-- Dependencies: 240
-- Name: cities_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.cities_id_seq OWNED BY public.cities.id;


--
-- TOC entry 255 (class 1259 OID 150375)
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
-- TOC entry 254 (class 1259 OID 150374)
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
-- TOC entry 5479 (class 0 OID 0)
-- Dependencies: 254
-- Name: conversations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.conversations_id_seq OWNED BY public.conversations.id;


--
-- TOC entry 233 (class 1259 OID 150052)
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
-- TOC entry 232 (class 1259 OID 150051)
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
-- TOC entry 5480 (class 0 OID 0)
-- Dependencies: 232
-- Name: countries_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.countries_id_seq OWNED BY public.countries.id;


--
-- TOC entry 231 (class 1259 OID 150033)
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
-- TOC entry 230 (class 1259 OID 150032)
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
-- TOC entry 5481 (class 0 OID 0)
-- Dependencies: 230
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- TOC entry 251 (class 1259 OID 150299)
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
-- TOC entry 250 (class 1259 OID 150298)
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
-- TOC entry 5482 (class 0 OID 0)
-- Dependencies: 250
-- Name: favorites_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.favorites_id_seq OWNED BY public.favorites.id;


--
-- TOC entry 229 (class 1259 OID 150018)
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
-- TOC entry 228 (class 1259 OID 150003)
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
-- TOC entry 227 (class 1259 OID 150002)
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
-- TOC entry 5483 (class 0 OID 0)
-- Dependencies: 227
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- TOC entry 253 (class 1259 OID 150325)
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
-- TOC entry 252 (class 1259 OID 150324)
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
-- TOC entry 5484 (class 0 OID 0)
-- Dependencies: 252
-- Name: leads_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.leads_id_seq OWNED BY public.leads.id;


--
-- TOC entry 257 (class 1259 OID 150406)
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
-- TOC entry 256 (class 1259 OID 150405)
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
-- TOC entry 5485 (class 0 OID 0)
-- Dependencies: 256
-- Name: messages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.messages_id_seq OWNED BY public.messages.id;


--
-- TOC entry 220 (class 1259 OID 143370)
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- TOC entry 219 (class 1259 OID 143369)
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
-- TOC entry 5486 (class 0 OID 0)
-- Dependencies: 219
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- TOC entry 270 (class 1259 OID 150540)
-- Name: model_has_permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.model_has_permissions (
    permission_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL
);


ALTER TABLE public.model_has_permissions OWNER TO postgres;

--
-- TOC entry 271 (class 1259 OID 150554)
-- Name: model_has_roles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.model_has_roles (
    role_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL
);


ALTER TABLE public.model_has_roles OWNER TO postgres;

--
-- TOC entry 237 (class 1259 OID 150076)
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
-- TOC entry 236 (class 1259 OID 150075)
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
-- TOC entry 5487 (class 0 OID 0)
-- Dependencies: 236
-- Name: municipalities_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.municipalities_id_seq OWNED BY public.municipalities.id;


--
-- TOC entry 239 (class 1259 OID 150091)
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
-- TOC entry 238 (class 1259 OID 150090)
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
-- TOC entry 5488 (class 0 OID 0)
-- Dependencies: 238
-- Name: parishes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.parishes_id_seq OWNED BY public.parishes.id;


--
-- TOC entry 223 (class 1259 OID 149959)
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO postgres;

--
-- TOC entry 267 (class 1259 OID 150513)
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
-- TOC entry 266 (class 1259 OID 150512)
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
-- TOC entry 5489 (class 0 OID 0)
-- Dependencies: 266
-- Name: permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.permissions_id_seq OWNED BY public.permissions.id;


--
-- TOC entry 265 (class 1259 OID 150495)
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
-- TOC entry 264 (class 1259 OID 150494)
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
-- TOC entry 5490 (class 0 OID 0)
-- Dependencies: 264
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.personal_access_tokens_id_seq OWNED BY public.personal_access_tokens.id;


--
-- TOC entry 245 (class 1259 OID 150139)
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
-- TOC entry 244 (class 1259 OID 150138)
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
-- TOC entry 5491 (class 0 OID 0)
-- Dependencies: 244
-- Name: properties_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.properties_id_seq OWNED BY public.properties.id;


--
-- TOC entry 247 (class 1259 OID 150227)
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
-- TOC entry 246 (class 1259 OID 150226)
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
-- TOC entry 5492 (class 0 OID 0)
-- Dependencies: 246
-- Name: property_images_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.property_images_id_seq OWNED BY public.property_images.id;


--
-- TOC entry 276 (class 1259 OID 150603)
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
-- TOC entry 275 (class 1259 OID 150602)
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
-- TOC entry 5493 (class 0 OID 0)
-- Dependencies: 275
-- Name: report_snapshots_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.report_snapshots_id_seq OWNED BY public.report_snapshots.id;


--
-- TOC entry 272 (class 1259 OID 150568)
-- Name: role_has_permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.role_has_permissions (
    permission_id bigint NOT NULL,
    role_id bigint NOT NULL
);


ALTER TABLE public.role_has_permissions OWNER TO postgres;

--
-- TOC entry 269 (class 1259 OID 150527)
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
-- TOC entry 268 (class 1259 OID 150526)
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
-- TOC entry 5494 (class 0 OID 0)
-- Dependencies: 268
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


--
-- TOC entry 284 (class 1259 OID 150712)
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
-- TOC entry 283 (class 1259 OID 150711)
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
-- TOC entry 5495 (class 0 OID 0)
-- Dependencies: 283
-- Name: service_galleries_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.service_galleries_id_seq OWNED BY public.service_galleries.id;


--
-- TOC entry 282 (class 1259 OID 150690)
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
-- TOC entry 281 (class 1259 OID 150689)
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
-- TOC entry 5496 (class 0 OID 0)
-- Dependencies: 281
-- Name: services_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.services_id_seq OWNED BY public.services.id;


--
-- TOC entry 224 (class 1259 OID 149968)
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
-- TOC entry 259 (class 1259 OID 150438)
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
-- TOC entry 258 (class 1259 OID 150437)
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
-- TOC entry 5497 (class 0 OID 0)
-- Dependencies: 258
-- Name: settings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.settings_id_seq OWNED BY public.settings.id;


--
-- TOC entry 278 (class 1259 OID 150630)
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
    footer_text text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.site_configurations OWNER TO postgres;

--
-- TOC entry 277 (class 1259 OID 150629)
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
-- TOC entry 5498 (class 0 OID 0)
-- Dependencies: 277
-- Name: site_configurations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.site_configurations_id_seq OWNED BY public.site_configurations.id;


--
-- TOC entry 235 (class 1259 OID 150061)
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
-- TOC entry 234 (class 1259 OID 150060)
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
-- TOC entry 5499 (class 0 OID 0)
-- Dependencies: 234
-- Name: states_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.states_id_seq OWNED BY public.states.id;


--
-- TOC entry 263 (class 1259 OID 150474)
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
-- TOC entry 262 (class 1259 OID 150473)
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
-- TOC entry 5500 (class 0 OID 0)
-- Dependencies: 262
-- Name: user_notifications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.user_notifications_id_seq OWNED BY public.user_notifications.id;


--
-- TOC entry 222 (class 1259 OID 149924)
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
-- TOC entry 5501 (class 0 OID 0)
-- Dependencies: 222
-- Name: COLUMN users.id_type; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.users.id_type IS 'V, E, J, P';


--
-- TOC entry 221 (class 1259 OID 149923)
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
-- TOC entry 5502 (class 0 OID 0)
-- Dependencies: 221
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- TOC entry 4978 (class 2604 OID 150589)
-- Name: account_reactivation_tokens id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.account_reactivation_tokens ALTER COLUMN id SET DEFAULT nextval('public.account_reactivation_tokens_id_seq'::regclass);


--
-- TOC entry 4988 (class 2604 OID 150655)
-- Name: appointment_settings id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.appointment_settings ALTER COLUMN id SET DEFAULT nextval('public.appointment_settings_id_seq'::regclass);


--
-- TOC entry 4954 (class 2604 OID 150255)
-- Name: appointments id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.appointments ALTER COLUMN id SET DEFAULT nextval('public.appointments_id_seq'::regclass);


--
-- TOC entry 4972 (class 2604 OID 150460)
-- Name: audit_logs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.audit_logs ALTER COLUMN id SET DEFAULT nextval('public.audit_logs_id_seq'::regclass);


--
-- TOC entry 4941 (class 2604 OID 150124)
-- Name: categories id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categories ALTER COLUMN id SET DEFAULT nextval('public.categories_id_seq'::regclass);


--
-- TOC entry 4940 (class 2604 OID 150109)
-- Name: cities id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cities ALTER COLUMN id SET DEFAULT nextval('public.cities_id_seq'::regclass);


--
-- TOC entry 4964 (class 2604 OID 150378)
-- Name: conversations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations ALTER COLUMN id SET DEFAULT nextval('public.conversations_id_seq'::regclass);


--
-- TOC entry 4934 (class 2604 OID 150055)
-- Name: countries id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.countries ALTER COLUMN id SET DEFAULT nextval('public.countries_id_seq'::regclass);


--
-- TOC entry 4932 (class 2604 OID 150036)
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- TOC entry 4958 (class 2604 OID 150302)
-- Name: favorites id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.favorites ALTER COLUMN id SET DEFAULT nextval('public.favorites_id_seq'::regclass);


--
-- TOC entry 4931 (class 2604 OID 150006)
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- TOC entry 4959 (class 2604 OID 150328)
-- Name: leads id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leads ALTER COLUMN id SET DEFAULT nextval('public.leads_id_seq'::regclass);


--
-- TOC entry 4966 (class 2604 OID 150409)
-- Name: messages id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.messages ALTER COLUMN id SET DEFAULT nextval('public.messages_id_seq'::regclass);


--
-- TOC entry 4927 (class 2604 OID 143373)
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- TOC entry 4938 (class 2604 OID 150079)
-- Name: municipalities id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.municipalities ALTER COLUMN id SET DEFAULT nextval('public.municipalities_id_seq'::regclass);


--
-- TOC entry 4939 (class 2604 OID 150094)
-- Name: parishes id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.parishes ALTER COLUMN id SET DEFAULT nextval('public.parishes_id_seq'::regclass);


--
-- TOC entry 4976 (class 2604 OID 150516)
-- Name: permissions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions ALTER COLUMN id SET DEFAULT nextval('public.permissions_id_seq'::regclass);


--
-- TOC entry 4975 (class 2604 OID 150498)
-- Name: personal_access_tokens id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens ALTER COLUMN id SET DEFAULT nextval('public.personal_access_tokens_id_seq'::regclass);


--
-- TOC entry 4944 (class 2604 OID 150142)
-- Name: properties id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.properties ALTER COLUMN id SET DEFAULT nextval('public.properties_id_seq'::regclass);


--
-- TOC entry 4951 (class 2604 OID 150230)
-- Name: property_images id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.property_images ALTER COLUMN id SET DEFAULT nextval('public.property_images_id_seq'::regclass);


--
-- TOC entry 4979 (class 2604 OID 150606)
-- Name: report_snapshots id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.report_snapshots ALTER COLUMN id SET DEFAULT nextval('public.report_snapshots_id_seq'::regclass);


--
-- TOC entry 4977 (class 2604 OID 150530)
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- TOC entry 5000 (class 2604 OID 150715)
-- Name: service_galleries id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.service_galleries ALTER COLUMN id SET DEFAULT nextval('public.service_galleries_id_seq'::regclass);


--
-- TOC entry 4995 (class 2604 OID 150693)
-- Name: services id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.services ALTER COLUMN id SET DEFAULT nextval('public.services_id_seq'::regclass);


--
-- TOC entry 4968 (class 2604 OID 150441)
-- Name: settings id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.settings ALTER COLUMN id SET DEFAULT nextval('public.settings_id_seq'::regclass);


--
-- TOC entry 4981 (class 2604 OID 150633)
-- Name: site_configurations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.site_configurations ALTER COLUMN id SET DEFAULT nextval('public.site_configurations_id_seq'::regclass);


--
-- TOC entry 4937 (class 2604 OID 150064)
-- Name: states id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.states ALTER COLUMN id SET DEFAULT nextval('public.states_id_seq'::regclass);


--
-- TOC entry 4973 (class 2604 OID 150477)
-- Name: user_notifications id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_notifications ALTER COLUMN id SET DEFAULT nextval('public.user_notifications_id_seq'::regclass);


--
-- TOC entry 4928 (class 2604 OID 149927)
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- TOC entry 5456 (class 0 OID 150586)
-- Dependencies: 274
-- Data for Name: account_reactivation_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.account_reactivation_tokens (id, email, token, created_at) FROM stdin;
\.


--
-- TOC entry 5462 (class 0 OID 150652)
-- Dependencies: 280
-- Data for Name: appointment_settings; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.appointment_settings (id, user_id, is_active, daily_config, valid_from, valid_to, apply_always, slot_duration, break_duration, notify_client, reminder_minutes, exceptions, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5431 (class 0 OID 150252)
-- Dependencies: 249
-- Data for Name: appointments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.appointments (id, user_id, property_id, asesor_id, scheduled_date, end_date, status, contact_name, contact_phone, contact_email, message, notes, result_notes, client_attended, property_sold, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5443 (class 0 OID 150457)
-- Dependencies: 261
-- Data for Name: audit_logs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.audit_logs (id, user_id, subject_type, subject_id, old_values, new_values, description, ip_address, user_agent, created_at, updated_at, action, event, auditable_type, auditable_id, tags, url, user_type) FROM stdin;
1	1	App\\Models\\User	1	\N	\N	Inicio de sesión de Carlos	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36	2026-07-19 04:06:58	2026-07-19 04:06:58	login	login	\N	\N	\N	http://127.0.0.1:8000/login	\N
2	1	App\\Models\\SiteConfiguration	1	"{\\"id\\":1,\\"hero_badge\\":\\"Exclusividad & Confort\\",\\"hero_title_line1\\":\\"El Arte de\\",\\"hero_title_line2\\":\\"Vivir Bien\\",\\"hero_subtitle\\":\\"Descubre una curadur\\\\u00eda exclusiva de propiedades de lujo en las mejores zonas de Venezuela.\\",\\"hero_images\\":[],\\"hero_image_paths\\":null,\\"featured_badge\\":\\"Colecci\\\\u00f3n Exclusiva\\",\\"featured_title\\":\\"Propiedades Destacadas\\",\\"featured_properties\\":[],\\"support_whatsapp\\":\\"58XXXXXXXXX\\",\\"support_instagram\\":\\"msoinmobiliaria\\",\\"support_phone\\":null,\\"support_email\\":null,\\"footer_text\\":\\"\\\\u00a9 2026 MSO Inmobiliaria. Todos los derechos reservados.\\",\\"created_at\\":\\"2026-07-19T04:06:50.000000Z\\",\\"updated_at\\":\\"2026-07-19T04:06:50.000000Z\\"}"	"{\\"id\\":1,\\"hero_badge\\":\\"Exclusividad & Confort\\",\\"hero_title_line1\\":\\"El Arte de\\",\\"hero_title_line2\\":\\"Vivir Bien\\",\\"hero_subtitle\\":\\"Descubre una curadur\\\\u00eda exclusiva de propiedades de lujo en las mejores zonas de Venezuela.\\",\\"hero_images\\":[\\"http:\\\\/\\\\/127.0.0.1:8000\\\\/storage\\\\/hero-images\\\\/AWESZn6TvDdZB5leW9Bo5VuTrWd5AhmyENJ2Lc7h.jpg\\"],\\"hero_image_paths\\":null,\\"featured_badge\\":\\"Colecci\\\\u00f3n Exclusiva\\",\\"featured_title\\":\\"Propiedades Destacadas\\",\\"featured_properties\\":[],\\"support_whatsapp\\":\\"58XXXXXXXXX\\",\\"support_instagram\\":\\"msoinmobiliaria\\",\\"support_phone\\":null,\\"support_email\\":null,\\"footer_text\\":\\"\\\\u00a9 2026 MSO Inmobiliaria. Todos los derechos reservados.\\",\\"created_at\\":\\"2026-07-19T04:06:50.000000Z\\",\\"updated_at\\":\\"2026-07-19T04:07:15.000000Z\\"}"	Carlos Rodríguez ACTUALIZÓ SiteConfiguration '#1'. Cambios: imágenes del hero: '[]' → '["http:\\/\\/127.0.0.1:8000\\/storage\\/hero-images\\/AWESZn6TvDdZB5leW9Bo5VuTrWd5AhmyENJ2Lc7h.jpg"]'	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36	2026-07-19 04:07:15	2026-07-19 04:07:15	updated	updated	App\\Models\\SiteConfiguration	1	\N	http://127.0.0.1:8000/admin/config	App\\Models\\User
3	1	App\\Models\\SiteConfiguration	1	"{\\"id\\":1,\\"hero_badge\\":\\"Exclusividad & Confort\\",\\"hero_title_line1\\":\\"El Arte de\\",\\"hero_title_line2\\":\\"Vivir Bien\\",\\"hero_subtitle\\":\\"Descubre una curadur\\\\u00eda exclusiva de propiedades de lujo en las mejores zonas de Venezuela.\\",\\"hero_images\\":[\\"http:\\\\/\\\\/127.0.0.1:8000\\\\/storage\\\\/hero-images\\\\/AWESZn6TvDdZB5leW9Bo5VuTrWd5AhmyENJ2Lc7h.jpg\\"],\\"hero_image_paths\\":null,\\"featured_badge\\":\\"Colecci\\\\u00f3n Exclusiva\\",\\"featured_title\\":\\"Propiedades Destacadas\\",\\"featured_properties\\":[],\\"support_whatsapp\\":\\"58XXXXXXXXX\\",\\"support_instagram\\":\\"msoinmobiliaria\\",\\"support_phone\\":null,\\"support_email\\":null,\\"footer_text\\":\\"\\\\u00a9 2026 MSO Inmobiliaria. Todos los derechos reservados.\\",\\"created_at\\":\\"2026-07-19T04:06:50.000000Z\\",\\"updated_at\\":\\"2026-07-19T04:07:15.000000Z\\"}"	"{\\"id\\":1,\\"hero_badge\\":\\"Exclusividad & Confort\\",\\"hero_title_line1\\":\\"El Arte de\\",\\"hero_title_line2\\":\\"Vivir Bien\\",\\"hero_subtitle\\":\\"Descubre una curadur\\\\u00eda exclusiva de propiedades de lujo en las mejores zonas de Venezuela.\\",\\"hero_images\\":[\\"http:\\\\/\\\\/127.0.0.1:8000\\\\/storage\\\\/hero-images\\\\/AWESZn6TvDdZB5leW9Bo5VuTrWd5AhmyENJ2Lc7h.jpg\\",\\"http:\\\\/\\\\/127.0.0.1:8000\\\\/storage\\\\/hero-images\\\\/2g2uzs44O3UQvNnT2yiFYG3Kilr6KdOhejQHIBaD.jpg\\"],\\"hero_image_paths\\":null,\\"featured_badge\\":\\"Colecci\\\\u00f3n Exclusiva\\",\\"featured_title\\":\\"Propiedades Destacadas\\",\\"featured_properties\\":[],\\"support_whatsapp\\":\\"58XXXXXXXXX\\",\\"support_instagram\\":\\"msoinmobiliaria\\",\\"support_phone\\":null,\\"support_email\\":null,\\"footer_text\\":\\"\\\\u00a9 2026 MSO Inmobiliaria. Todos los derechos reservados.\\",\\"created_at\\":\\"2026-07-19T04:06:50.000000Z\\",\\"updated_at\\":\\"2026-07-19T04:07:38.000000Z\\"}"	Carlos Rodríguez ACTUALIZÓ SiteConfiguration '#1'. Cambios: imágenes del hero: '[http://127.0.0.1:8000/storage/hero-images/AWESZn6TvDdZB5leW9Bo5VuTrWd5AhmyENJ2Lc7h.jpg]' → '["http:\\/\\/127.0.0.1:8000\\/storage\\/hero-images\\/AWESZn6TvDdZB5leW9Bo5VuTrWd5AhmyENJ2Lc7h.jpg","http:\\/\\/127.0.0.1:8000\\/storage\\/hero-images\\/2g2uzs44O3UQvNnT2yiFYG3Kilr6KdOhejQHIBaD.jpg"]'	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36	2026-07-19 04:07:38	2026-07-19 04:07:38	updated	updated	App\\Models\\SiteConfiguration	1	\N	http://127.0.0.1:8000/admin/config	App\\Models\\User
\.


--
-- TOC entry 5407 (class 0 OID 149980)
-- Dependencies: 225
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache (key, value, expiration) FROM stdin;
laravel-cache-api_countries_all	TzozOToiSWxsdW1pbmF0ZVxEYXRhYmFzZVxFbG9xdWVudFxDb2xsZWN0aW9uIjoyOntzOjg6IgAqAGl0ZW1zIjthOjE6e2k6MDtPOjE4OiJBcHBcTW9kZWxzXENvdW50cnkiOjMzOntzOjEzOiIAKgBjb25uZWN0aW9uIjtzOjU6InBnc3FsIjtzOjg6IgAqAHRhYmxlIjtzOjk6ImNvdW50cmllcyI7czoxMzoiACoAcHJpbWFyeUtleSI7czoyOiJpZCI7czoxMDoiACoAa2V5VHlwZSI7czozOiJpbnQiO3M6MTI6ImluY3JlbWVudGluZyI7YjoxO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjI6e3M6MjoiaWQiO2k6MTtzOjQ6Im5hbWUiO3M6OToiVmVuZXp1ZWxhIjt9czoxMToiACoAb3JpZ2luYWwiO2E6Mjp7czoyOiJpZCI7aToxO3M6NDoibmFtZSI7czo5OiJWZW5lenVlbGEiO31zOjEwOiIAKgBjaGFuZ2VzIjthOjA6e31zOjExOiIAKgBwcmV2aW91cyI7YTowOnt9czo4OiIAKgBjYXN0cyI7YTowOnt9czoxNzoiACoAY2xhc3NDYXN0Q2FjaGUiO2E6MDp7fXM6MjE6IgAqAGF0dHJpYnV0ZUNhc3RDYWNoZSI7YTowOnt9czoxMzoiACoAZGF0ZUZvcm1hdCI7TjtzOjEwOiIAKgBhcHBlbmRzIjthOjA6e31zOjE5OiIAKgBkaXNwYXRjaGVzRXZlbnRzIjthOjA6e31zOjE0OiIAKgBvYnNlcnZhYmxlcyI7YTowOnt9czoxMjoiACoAcmVsYXRpb25zIjthOjA6e31zOjEwOiIAKgB0b3VjaGVzIjthOjA6e31zOjI3OiIAKgByZWxhdGlvbkF1dG9sb2FkQ2FsbGJhY2siO047czoyNjoiACoAcmVsYXRpb25BdXRvbG9hZENvbnRleHQiO047czoxMDoidGltZXN0YW1wcyI7YjoxO3M6MTM6InVzZXNVbmlxdWVJZHMiO2I6MDtzOjk6IgAqAGhpZGRlbiI7YTowOnt9czoxMDoiACoAdmlzaWJsZSI7YTowOnt9czoxMToiACoAZmlsbGFibGUiO2E6Njp7aTowO3M6NDoibmFtZSI7aToxO3M6NDoiY29kZSI7aToyO3M6MTA6InBob25lX2NvZGUiO2k6MztzOjEyOiJwaG9uZV9mb3JtYXQiO2k6NDtzOjE2OiJwaG9uZV9taW5fbGVuZ3RoIjtpOjU7czoxNjoicGhvbmVfbWF4X2xlbmd0aCI7fXM6MTA6IgAqAGd1YXJkZWQiO2E6MTp7aTowO3M6MToiKiI7fX19czoyODoiACoAZXNjYXBlV2hlbkNhc3RpbmdUb1N0cmluZyI7YjowO30=	1784437611
laravel-cache-api_countries_list	TzozOToiSWxsdW1pbmF0ZVxEYXRhYmFzZVxFbG9xdWVudFxDb2xsZWN0aW9uIjoyOntzOjg6IgAqAGl0ZW1zIjthOjE6e2k6MDtPOjE4OiJBcHBcTW9kZWxzXENvdW50cnkiOjMzOntzOjEzOiIAKgBjb25uZWN0aW9uIjtzOjU6InBnc3FsIjtzOjg6IgAqAHRhYmxlIjtzOjk6ImNvdW50cmllcyI7czoxMzoiACoAcHJpbWFyeUtleSI7czoyOiJpZCI7czoxMDoiACoAa2V5VHlwZSI7czozOiJpbnQiO3M6MTI6ImluY3JlbWVudGluZyI7YjoxO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjI6e3M6MjoiaWQiO2k6MTtzOjQ6Im5hbWUiO3M6OToiVmVuZXp1ZWxhIjt9czoxMToiACoAb3JpZ2luYWwiO2E6Mjp7czoyOiJpZCI7aToxO3M6NDoibmFtZSI7czo5OiJWZW5lenVlbGEiO31zOjEwOiIAKgBjaGFuZ2VzIjthOjA6e31zOjExOiIAKgBwcmV2aW91cyI7YTowOnt9czo4OiIAKgBjYXN0cyI7YTowOnt9czoxNzoiACoAY2xhc3NDYXN0Q2FjaGUiO2E6MDp7fXM6MjE6IgAqAGF0dHJpYnV0ZUNhc3RDYWNoZSI7YTowOnt9czoxMzoiACoAZGF0ZUZvcm1hdCI7TjtzOjEwOiIAKgBhcHBlbmRzIjthOjA6e31zOjE5OiIAKgBkaXNwYXRjaGVzRXZlbnRzIjthOjA6e31zOjE0OiIAKgBvYnNlcnZhYmxlcyI7YTowOnt9czoxMjoiACoAcmVsYXRpb25zIjthOjA6e31zOjEwOiIAKgB0b3VjaGVzIjthOjA6e31zOjI3OiIAKgByZWxhdGlvbkF1dG9sb2FkQ2FsbGJhY2siO047czoyNjoiACoAcmVsYXRpb25BdXRvbG9hZENvbnRleHQiO047czoxMDoidGltZXN0YW1wcyI7YjoxO3M6MTM6InVzZXNVbmlxdWVJZHMiO2I6MDtzOjk6IgAqAGhpZGRlbiI7YTowOnt9czoxMDoiACoAdmlzaWJsZSI7YTowOnt9czoxMToiACoAZmlsbGFibGUiO2E6Njp7aTowO3M6NDoibmFtZSI7aToxO3M6NDoiY29kZSI7aToyO3M6MTA6InBob25lX2NvZGUiO2k6MztzOjEyOiJwaG9uZV9mb3JtYXQiO2k6NDtzOjE2OiJwaG9uZV9taW5fbGVuZ3RoIjtpOjU7czoxNjoicGhvbmVfbWF4X2xlbmd0aCI7fXM6MTA6IgAqAGd1YXJkZWQiO2E6MTp7aTowO3M6MToiKiI7fX19czoyODoiACoAZXNjYXBlV2hlbkNhc3RpbmdUb1N0cmluZyI7YjowO30=	1784520412
laravel-cache-api_phone_codes	TzoyODoiSWxsdW1pbmF0ZVxIdHRwXEpzb25SZXNwb25zZSI6MTE6e3M6NzoiaGVhZGVycyI7Tzo1MDoiU3ltZm9ueVxDb21wb25lbnRcSHR0cEZvdW5kYXRpb25cUmVzcG9uc2VIZWFkZXJCYWciOjU6e3M6MTA6IgAqAGhlYWRlcnMiO2E6Mzp7czoxMzoiY2FjaGUtY29udHJvbCI7YToxOntpOjA7czoxNzoibm8tY2FjaGUsIHByaXZhdGUiO31zOjQ6ImRhdGUiO2E6MTp7aTowO3M6Mjk6IlN1biwgMTkgSnVsIDIwMjYgMDQ6MDY6NTIgR01UIjt9czoxMjoiY29udGVudC10eXBlIjthOjE6e2k6MDtzOjE2OiJhcHBsaWNhdGlvbi9qc29uIjt9fXM6MTU6IgAqAGNhY2hlQ29udHJvbCI7YTowOnt9czoyMzoiACoAY29tcHV0ZWRDYWNoZUNvbnRyb2wiO2E6Mjp7czo4OiJuby1jYWNoZSI7YjoxO3M6NzoicHJpdmF0ZSI7YjoxO31zOjEwOiIAKgBjb29raWVzIjthOjA6e31zOjE0OiIAKgBoZWFkZXJOYW1lcyI7YTozOntzOjEzOiJjYWNoZS1jb250cm9sIjtzOjEzOiJDYWNoZS1Db250cm9sIjtzOjQ6ImRhdGUiO3M6NDoiRGF0ZSI7czoxMjoiY29udGVudC10eXBlIjtzOjEyOiJDb250ZW50LVR5cGUiO319czoxMDoiACoAY29udGVudCI7czoxMzM6Ilt7ImlkIjoxLCJuYW1lIjoiVmVuZXp1ZWxhIiwiaXNvIjoiVkVOIiwicGhvbmVfY29kZSI6Iis1OCIsInBob25lX2Zvcm1hdCI6IjAwMC0wMDAwMDAwIiwicGhvbmVfbWluX2xlbmd0aCI6MTAsInBob25lX21heF9sZW5ndGgiOjEwfV0iO3M6MTA6IgAqAHZlcnNpb24iO3M6MzoiMS4wIjtzOjEzOiIAKgBzdGF0dXNDb2RlIjtpOjIwMDtzOjEzOiIAKgBzdGF0dXNUZXh0IjtzOjI6Ik9LIjtzOjEwOiIAKgBjaGFyc2V0IjtOO3M6NzoiACoAZGF0YSI7czoxMzM6Ilt7ImlkIjoxLCJuYW1lIjoiVmVuZXp1ZWxhIiwiaXNvIjoiVkVOIiwicGhvbmVfY29kZSI6Iis1OCIsInBob25lX2Zvcm1hdCI6IjAwMC0wMDAwMDAwIiwicGhvbmVfbWluX2xlbmd0aCI6MTAsInBob25lX21heF9sZW5ndGgiOjEwfV0iO3M6MTE6IgAqAGNhbGxiYWNrIjtOO3M6MTg6IgAqAGVuY29kaW5nT3B0aW9ucyI7aTowO3M6ODoib3JpZ2luYWwiO086Mzk6IklsbHVtaW5hdGVcRGF0YWJhc2VcRWxvcXVlbnRcQ29sbGVjdGlvbiI6Mjp7czo4OiIAKgBpdGVtcyI7YToxOntpOjA7TzoxODoiQXBwXE1vZGVsc1xDb3VudHJ5IjozMzp7czoxMzoiACoAY29ubmVjdGlvbiI7czo1OiJwZ3NxbCI7czo4OiIAKgB0YWJsZSI7czo5OiJjb3VudHJpZXMiO3M6MTM6IgAqAHByaW1hcnlLZXkiO3M6MjoiaWQiO3M6MTA6IgAqAGtleVR5cGUiO3M6MzoiaW50IjtzOjEyOiJpbmNyZW1lbnRpbmciO2I6MTtzOjc6IgAqAHdpdGgiO2E6MDp7fXM6MTI6IgAqAHdpdGhDb3VudCI7YTowOnt9czoxOToicHJldmVudHNMYXp5TG9hZGluZyI7YjowO3M6MTA6IgAqAHBlclBhZ2UiO2k6MTU7czo2OiJleGlzdHMiO2I6MTtzOjE4OiJ3YXNSZWNlbnRseUNyZWF0ZWQiO2I6MDtzOjI4OiIAKgBlc2NhcGVXaGVuQ2FzdGluZ1RvU3RyaW5nIjtiOjA7czoxMzoiACoAYXR0cmlidXRlcyI7YTo3OntzOjI6ImlkIjtpOjE7czo0OiJuYW1lIjtzOjk6IlZlbmV6dWVsYSI7czozOiJpc28iO3M6MzoiVkVOIjtzOjEwOiJwaG9uZV9jb2RlIjtzOjM6Iis1OCI7czoxMjoicGhvbmVfZm9ybWF0IjtzOjExOiIwMDAtMDAwMDAwMCI7czoxNjoicGhvbmVfbWluX2xlbmd0aCI7aToxMDtzOjE2OiJwaG9uZV9tYXhfbGVuZ3RoIjtpOjEwO31zOjExOiIAKgBvcmlnaW5hbCI7YTo3OntzOjI6ImlkIjtpOjE7czo0OiJuYW1lIjtzOjk6IlZlbmV6dWVsYSI7czozOiJpc28iO3M6MzoiVkVOIjtzOjEwOiJwaG9uZV9jb2RlIjtzOjM6Iis1OCI7czoxMjoicGhvbmVfZm9ybWF0IjtzOjExOiIwMDAtMDAwMDAwMCI7czoxNjoicGhvbmVfbWluX2xlbmd0aCI7aToxMDtzOjE2OiJwaG9uZV9tYXhfbGVuZ3RoIjtpOjEwO31zOjEwOiIAKgBjaGFuZ2VzIjthOjA6e31zOjExOiIAKgBwcmV2aW91cyI7YTowOnt9czo4OiIAKgBjYXN0cyI7YTowOnt9czoxNzoiACoAY2xhc3NDYXN0Q2FjaGUiO2E6MDp7fXM6MjE6IgAqAGF0dHJpYnV0ZUNhc3RDYWNoZSI7YTowOnt9czoxMzoiACoAZGF0ZUZvcm1hdCI7TjtzOjEwOiIAKgBhcHBlbmRzIjthOjA6e31zOjE5OiIAKgBkaXNwYXRjaGVzRXZlbnRzIjthOjA6e31zOjE0OiIAKgBvYnNlcnZhYmxlcyI7YTowOnt9czoxMjoiACoAcmVsYXRpb25zIjthOjA6e31zOjEwOiIAKgB0b3VjaGVzIjthOjA6e31zOjI3OiIAKgByZWxhdGlvbkF1dG9sb2FkQ2FsbGJhY2siO047czoyNjoiACoAcmVsYXRpb25BdXRvbG9hZENvbnRleHQiO047czoxMDoidGltZXN0YW1wcyI7YjoxO3M6MTM6InVzZXNVbmlxdWVJZHMiO2I6MDtzOjk6IgAqAGhpZGRlbiI7YTowOnt9czoxMDoiACoAdmlzaWJsZSI7YTowOnt9czoxMToiACoAZmlsbGFibGUiO2E6Njp7aTowO3M6NDoibmFtZSI7aToxO3M6NDoiY29kZSI7aToyO3M6MTA6InBob25lX2NvZGUiO2k6MztzOjEyOiJwaG9uZV9mb3JtYXQiO2k6NDtzOjE2OiJwaG9uZV9taW5fbGVuZ3RoIjtpOjU7czoxNjoicGhvbmVfbWF4X2xlbmd0aCI7fXM6MTA6IgAqAGd1YXJkZWQiO2E6MTp7aTowO3M6MToiKiI7fX19czoyODoiACoAZXNjYXBlV2hlbkNhc3RpbmdUb1N0cmluZyI7YjowO31zOjk6ImV4Y2VwdGlvbiI7Tjt9	1784520412
laravel-cache-5c785c036466adea360111aa28563bfd556b5fba:timer	i:1784434077;	1784434077
laravel-cache-5c785c036466adea360111aa28563bfd556b5fba	i:1;	1784434077
laravel-cache-user_roles_1	a:1:{i:0;s:11:"Super Admin";}	1784434321
laravel-cache-spatie.permission.cache	a:3:{s:5:"alias";a:4:{s:1:"a";s:2:"id";s:1:"b";s:4:"name";s:1:"c";s:10:"guard_name";s:1:"r";s:5:"roles";}s:11:"permissions";a:51:{i:0;a:4:{s:1:"a";i:1;s:1:"b";s:20:"ver panel de control";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:1;a:4:{s:1:"a";i:2;s:1:"b";s:12:"ver usuarios";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:2;a:4:{s:1:"a";i:3;s:1:"b";s:13:"crear usuario";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:3;a:4:{s:1:"a";i:4;s:1:"b";s:14:"editar usuario";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:4;a:4:{s:1:"a";i:5;s:1:"b";s:16:"eliminar usuario";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:5;a:4:{s:1:"a";i:6;s:1:"b";s:9:"ver roles";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:6;a:4:{s:1:"a";i:7;s:1:"b";s:9:"crear rol";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:7;a:4:{s:1:"a";i:8;s:1:"b";s:10:"editar rol";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:8;a:4:{s:1:"a";i:9;s:1:"b";s:12:"eliminar rol";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:9;a:4:{s:1:"a";i:10;s:1:"b";s:14:"ver categorias";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:10;a:4:{s:1:"a";i:11;s:1:"b";s:15:"crear categoria";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:11;a:4:{s:1:"a";i:12;s:1:"b";s:16:"editar categoria";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:12;a:4:{s:1:"a";i:13;s:1:"b";s:18:"eliminar categoria";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:13;a:4:{s:1:"a";i:14;s:1:"b";s:10:"ver paises";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:14;a:4:{s:1:"a";i:15;s:1:"b";s:12:"crear paises";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:15;a:4:{s:1:"a";i:16;s:1:"b";s:15:"eliminar paises";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:16;a:4:{s:1:"a";i:17;s:1:"b";s:11:"ver estados";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:17;a:4:{s:1:"a";i:18;s:1:"b";s:13:"crear estados";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:18;a:4:{s:1:"a";i:19;s:1:"b";s:16:"eliminar estados";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:19;a:4:{s:1:"a";i:20;s:1:"b";s:14:"ver municipios";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:20;a:4:{s:1:"a";i:21;s:1:"b";s:16:"crear municipios";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:21;a:4:{s:1:"a";i:22;s:1:"b";s:19:"eliminar municipios";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:22;a:4:{s:1:"a";i:23;s:1:"b";s:14:"ver parroquias";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:23;a:4:{s:1:"a";i:24;s:1:"b";s:16:"crear parroquias";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:24;a:4:{s:1:"a";i:25;s:1:"b";s:19:"eliminar parroquias";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:25;a:4:{s:1:"a";i:26;s:1:"b";s:12:"ver ciudades";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:26;a:4:{s:1:"a";i:27;s:1:"b";s:14:"crear ciudades";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:27;a:4:{s:1:"a";i:28;s:1:"b";s:17:"eliminar ciudades";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:28;a:4:{s:1:"a";i:29;s:1:"b";s:15:"ver propiedades";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:29;a:4:{s:1:"a";i:30;s:1:"b";s:15:"crear propiedad";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:30;a:4:{s:1:"a";i:31;s:1:"b";s:16:"editar propiedad";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:31;a:4:{s:1:"a";i:32;s:1:"b";s:18:"eliminar propiedad";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:32;a:4:{s:1:"a";i:33;s:1:"b";s:18:"publicar propiedad";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:33;a:4:{s:1:"a";i:34;s:1:"b";s:9:"ver citas";s:1:"c";s:3:"web";s:1:"r";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:34;a:4:{s:1:"a";i:35;s:1:"b";s:10:"crear cita";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:5;}}i:35;a:4:{s:1:"a";i:36;s:1:"b";s:11:"editar cita";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:36;a:4:{s:1:"a";i:37;s:1:"b";s:13:"eliminar cita";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:37;a:4:{s:1:"a";i:38;s:1:"b";s:9:"ver leads";s:1:"c";s:3:"web";s:1:"r";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:38;a:4:{s:1:"a";i:39;s:1:"b";s:10:"crear lead";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:39;a:4:{s:1:"a";i:40;s:1:"b";s:11:"editar lead";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:40;a:4:{s:1:"a";i:41;s:1:"b";s:13:"eliminar lead";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:41;a:4:{s:1:"a";i:42;s:1:"b";s:13:"ver servicios";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:42;a:4:{s:1:"a";i:43;s:1:"b";s:15:"crear servicios";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:43;a:4:{s:1:"a";i:44;s:1:"b";s:16:"editar servicios";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:44;a:4:{s:1:"a";i:45;s:1:"b";s:18:"eliminar servicios";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:45;a:4:{s:1:"a";i:46;s:1:"b";s:21:"ver logs de auditoria";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:4;}}i:46;a:4:{s:1:"a";i:47;s:1:"b";s:12:"ver reportes";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:47;a:4:{s:1:"a";i:48;s:1:"b";s:17:"exportar reportes";s:1:"c";s:3:"web";s:1:"r";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:48;a:4:{s:1:"a";i:49;s:1:"b";s:18:"ver configuración";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:49;a:4:{s:1:"a";i:50;s:1:"b";s:21:"editar configuración";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:50;a:4:{s:1:"a";i:51;s:1:"b";s:35:"actualizar configuracion telefonica";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}}s:5:"roles";a:5:{i:0;a:3:{s:1:"a";i:1;s:1:"b";s:11:"Super Admin";s:1:"c";s:3:"web";}i:1;a:3:{s:1:"a";i:2;s:1:"b";s:13:"Administrador";s:1:"c";s:3:"web";}i:2;a:3:{s:1:"a";i:3;s:1:"b";s:19:"Asesor Inmobiliario";s:1:"c";s:3:"web";}i:3;a:3:{s:1:"a";i:4;s:1:"b";s:7:"Auditor";s:1:"c";s:3:"web";}i:4;a:3:{s:1:"a";i:5;s:1:"b";s:7:"Cliente";s:1:"c";s:3:"web";}}}	1784520423
laravel-cache-356a192b7913b04c54574d18c28d46e6395428ab:timer	i:1784434127;	1784434127
laravel-cache-356a192b7913b04c54574d18c28d46e6395428ab	i:1;	1784434127
\.


--
-- TOC entry 5408 (class 0 OID 149991)
-- Dependencies: 226
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- TOC entry 5425 (class 0 OID 150121)
-- Dependencies: 243
-- Data for Name: categories; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.categories (id, name, slug, description, icon, is_active, "order", created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5423 (class 0 OID 150106)
-- Dependencies: 241
-- Data for Name: cities; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cities (id, parish_id, name, created_at, updated_at) FROM stdin;
1	1	Caracas	2026-07-19 04:05:49	2026-07-19 04:05:49
2	2	Caracas	2026-07-19 04:05:49	2026-07-19 04:05:49
3	3	Baruta	2026-07-19 04:05:49	2026-07-19 04:05:49
4	4	Valencia	2026-07-19 04:05:49	2026-07-19 04:05:49
\.


--
-- TOC entry 5437 (class 0 OID 150375)
-- Dependencies: 255
-- Data for Name: conversations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.conversations (id, client_id, asesor_id, subject, last_message_at, is_active, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5415 (class 0 OID 150052)
-- Dependencies: 233
-- Data for Name: countries; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.countries (id, name, code, created_at, updated_at, phone_code, phone_format, phone_min_length, phone_max_length) FROM stdin;
1	Venezuela	VEN	2026-07-19 04:05:49	2026-07-19 04:05:49	+58	000-0000000	10	10
\.


--
-- TOC entry 5413 (class 0 OID 150033)
-- Dependencies: 231
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- TOC entry 5433 (class 0 OID 150299)
-- Dependencies: 251
-- Data for Name: favorites; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.favorites (id, user_id, property_id, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5411 (class 0 OID 150018)
-- Dependencies: 229
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- TOC entry 5410 (class 0 OID 150003)
-- Dependencies: 228
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- TOC entry 5435 (class 0 OID 150325)
-- Dependencies: 253
-- Data for Name: leads; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.leads (id, user_id, property_id, asesor_id, name, email, phone, id_type, id_number, source, source_detail, interest_type, budget_min, budget_max, preferences, status, notes, last_contact, contact_count, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5439 (class 0 OID 150406)
-- Dependencies: 257
-- Data for Name: messages; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.messages (id, conversation_id, user_id, content, is_read, read_at, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5402 (class 0 OID 143370)
-- Dependencies: 220
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.migrations (id, migration, batch) FROM stdin;
258	0001_01_01_000000_create_users_table	2
259	0001_01_01_000001_create_cache_table	2
260	0001_01_01_000002_create_jobs_table	2
261	2026_03_08_110106_create_countries_table	2
262	2026_03_08_110115_create_states_table	2
263	2026_03_08_110124_create_municipalities_table	2
264	2026_03_08_110130_create_parishes_table	2
265	2026_03_08_110137_create_cities_table	2
266	2026_03_09_195518_create_categories_table	2
267	2026_03_09_195545_create_properties_table	2
268	2026_03_09_195612_create_property_images_table	2
269	2026_03_09_195638_create_appointments_table	2
270	2026_03_09_195700_create_favorites_table	2
271	2026_03_09_195722_create_leads_table	2
272	2026_03_09_195742_create_conversations_table	2
273	2026_03_09_195802_create_messages_table	2
274	2026_03_09_195820_create_settings_table	2
275	2026_03_09_195840_create_audit_logs_table	2
276	2026_03_09_200049_create_notifications_table	2
277	2026_03_09_200639_create_personal_access_tokens_table	2
278	2026_03_09_200647_create_permission_tables	2
279	2026_03_25_020038_add_soft_deletes_to_users_table	2
280	2026_04_12_032519_create_account_reactivation_tokens_table	2
281	2026_04_12_152224_add_phone_code_to_countries_table	2
282	2026_06_06_201857_create_user_notifications_table	2
283	2026_06_08_053138_create_report_snapshots_table	2
284	2026_06_08_165415_fix_missing_audit_logs_action_column	2
285	2026_06_18_154630_create_site_configurations_table	2
286	2026_06_21_220255_create_appointment_settings_table	2
287	2026_06_23_131318_create_audits_table	2
64	2026_06_24_194659_add_missing_columns_to_audit_logs_table	1
288	2026_06_23_181559_create_services_table	2
289	2026_07_19_034818_fix_audit_logs_fields	2
\.


--
-- TOC entry 5452 (class 0 OID 150540)
-- Dependencies: 270
-- Data for Name: model_has_permissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.model_has_permissions (permission_id, model_type, model_id) FROM stdin;
\.


--
-- TOC entry 5453 (class 0 OID 150554)
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
-- TOC entry 5419 (class 0 OID 150076)
-- Dependencies: 237
-- Data for Name: municipalities; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.municipalities (id, state_id, name, created_at, updated_at) FROM stdin;
1	1	Libertador	2026-07-19 04:05:49	2026-07-19 04:05:49
2	2	Baruta	2026-07-19 04:05:49	2026-07-19 04:05:49
3	3	Valencia	2026-07-19 04:05:49	2026-07-19 04:05:49
\.


--
-- TOC entry 5421 (class 0 OID 150091)
-- Dependencies: 239
-- Data for Name: parishes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.parishes (id, municipality_id, name, created_at, updated_at) FROM stdin;
1	1	Altagracia	2026-07-19 04:05:49	2026-07-19 04:05:49
2	1	Catedral	2026-07-19 04:05:49	2026-07-19 04:05:49
3	2	Baruta	2026-07-19 04:05:49	2026-07-19 04:05:49
4	3	San José	2026-07-19 04:05:49	2026-07-19 04:05:49
\.


--
-- TOC entry 5405 (class 0 OID 149959)
-- Dependencies: 223
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- TOC entry 5449 (class 0 OID 150513)
-- Dependencies: 267
-- Data for Name: permissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.permissions (id, name, guard_name, created_at, updated_at) FROM stdin;
1	ver panel de control	web	2026-07-19 04:05:49	2026-07-19 04:05:49
2	ver usuarios	web	2026-07-19 04:05:49	2026-07-19 04:05:49
3	crear usuario	web	2026-07-19 04:05:49	2026-07-19 04:05:49
4	editar usuario	web	2026-07-19 04:05:49	2026-07-19 04:05:49
5	eliminar usuario	web	2026-07-19 04:05:49	2026-07-19 04:05:49
6	ver roles	web	2026-07-19 04:05:49	2026-07-19 04:05:49
7	crear rol	web	2026-07-19 04:05:49	2026-07-19 04:05:49
8	editar rol	web	2026-07-19 04:05:49	2026-07-19 04:05:49
9	eliminar rol	web	2026-07-19 04:05:49	2026-07-19 04:05:49
10	ver categorias	web	2026-07-19 04:05:49	2026-07-19 04:05:49
11	crear categoria	web	2026-07-19 04:05:49	2026-07-19 04:05:49
12	editar categoria	web	2026-07-19 04:05:49	2026-07-19 04:05:49
13	eliminar categoria	web	2026-07-19 04:05:49	2026-07-19 04:05:49
14	ver paises	web	2026-07-19 04:05:49	2026-07-19 04:05:49
15	crear paises	web	2026-07-19 04:05:49	2026-07-19 04:05:49
16	eliminar paises	web	2026-07-19 04:05:49	2026-07-19 04:05:49
17	ver estados	web	2026-07-19 04:05:49	2026-07-19 04:05:49
18	crear estados	web	2026-07-19 04:05:49	2026-07-19 04:05:49
19	eliminar estados	web	2026-07-19 04:05:49	2026-07-19 04:05:49
20	ver municipios	web	2026-07-19 04:05:49	2026-07-19 04:05:49
21	crear municipios	web	2026-07-19 04:05:49	2026-07-19 04:05:49
22	eliminar municipios	web	2026-07-19 04:05:49	2026-07-19 04:05:49
23	ver parroquias	web	2026-07-19 04:05:49	2026-07-19 04:05:49
24	crear parroquias	web	2026-07-19 04:05:49	2026-07-19 04:05:49
25	eliminar parroquias	web	2026-07-19 04:05:49	2026-07-19 04:05:49
26	ver ciudades	web	2026-07-19 04:05:49	2026-07-19 04:05:49
27	crear ciudades	web	2026-07-19 04:05:49	2026-07-19 04:05:49
28	eliminar ciudades	web	2026-07-19 04:05:49	2026-07-19 04:05:49
29	ver propiedades	web	2026-07-19 04:05:49	2026-07-19 04:05:49
30	crear propiedad	web	2026-07-19 04:05:49	2026-07-19 04:05:49
31	editar propiedad	web	2026-07-19 04:05:49	2026-07-19 04:05:49
32	eliminar propiedad	web	2026-07-19 04:05:49	2026-07-19 04:05:49
33	publicar propiedad	web	2026-07-19 04:05:49	2026-07-19 04:05:49
34	ver citas	web	2026-07-19 04:05:49	2026-07-19 04:05:49
35	crear cita	web	2026-07-19 04:05:49	2026-07-19 04:05:49
36	editar cita	web	2026-07-19 04:05:49	2026-07-19 04:05:49
37	eliminar cita	web	2026-07-19 04:05:49	2026-07-19 04:05:49
38	ver leads	web	2026-07-19 04:05:49	2026-07-19 04:05:49
39	crear lead	web	2026-07-19 04:05:49	2026-07-19 04:05:49
40	editar lead	web	2026-07-19 04:05:49	2026-07-19 04:05:49
41	eliminar lead	web	2026-07-19 04:05:49	2026-07-19 04:05:49
42	ver servicios	web	2026-07-19 04:05:49	2026-07-19 04:05:49
43	crear servicios	web	2026-07-19 04:05:49	2026-07-19 04:05:49
44	editar servicios	web	2026-07-19 04:05:49	2026-07-19 04:05:49
45	eliminar servicios	web	2026-07-19 04:05:49	2026-07-19 04:05:49
46	ver logs de auditoria	web	2026-07-19 04:05:49	2026-07-19 04:05:49
47	ver reportes	web	2026-07-19 04:05:49	2026-07-19 04:05:49
48	exportar reportes	web	2026-07-19 04:05:49	2026-07-19 04:05:49
49	ver configuración	web	2026-07-19 04:05:49	2026-07-19 04:05:49
50	editar configuración	web	2026-07-19 04:05:49	2026-07-19 04:05:49
51	actualizar configuracion telefonica	web	2026-07-19 04:05:49	2026-07-19 04:05:49
\.


--
-- TOC entry 5447 (class 0 OID 150495)
-- Dependencies: 265
-- Data for Name: personal_access_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.personal_access_tokens (id, tokenable_type, tokenable_id, name, token, abilities, last_used_at, expires_at, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5427 (class 0 OID 150139)
-- Dependencies: 245
-- Data for Name: properties; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.properties (id, title, description, price, price_currency, country_id, state_id, municipality_id, parish_id, city_id, address, location, sector, city, state, country, zip_code, bedrooms, bathrooms, parking_spaces, area, land_area, floors, year_built, type, status, features, user_id, category_id, views, inquiries, is_featured, featured_until, meta_data, created_at, updated_at, deleted_at) FROM stdin;
\.


--
-- TOC entry 5429 (class 0 OID 150227)
-- Dependencies: 247
-- Data for Name: property_images; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.property_images (id, property_id, image_path, thumbnail_path, caption, "order", is_primary, mime_type, size, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5458 (class 0 OID 150603)
-- Dependencies: 276
-- Data for Name: report_snapshots; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.report_snapshots (id, user_id, report_type, period, data, file_path, status, sent_at, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5454 (class 0 OID 150568)
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
-- TOC entry 5451 (class 0 OID 150527)
-- Dependencies: 269
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.roles (id, name, guard_name, created_at, updated_at) FROM stdin;
1	Super Admin	web	2026-07-19 04:05:49	2026-07-19 04:05:49
2	Administrador	web	2026-07-19 04:05:49	2026-07-19 04:05:49
3	Asesor Inmobiliario	web	2026-07-19 04:05:49	2026-07-19 04:05:49
4	Auditor	web	2026-07-19 04:05:49	2026-07-19 04:05:49
5	Cliente	web	2026-07-19 04:05:49	2026-07-19 04:05:49
\.


--
-- TOC entry 5466 (class 0 OID 150712)
-- Dependencies: 284
-- Data for Name: service_galleries; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.service_galleries (id, service_id, image_path, title, alt_text, "order", is_active, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5464 (class 0 OID 150690)
-- Dependencies: 282
-- Data for Name: services; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.services (id, title, slug, description, icon, color, badge, image, features, external_url, "order", is_active, is_featured, created_at, updated_at, deleted_at) FROM stdin;
\.


--
-- TOC entry 5406 (class 0 OID 149968)
-- Dependencies: 224
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
LlbcpDyqV987Ue1szYzAiCURDMkKBPmIjjEGJ8oB	1	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36	YTo0OntzOjY6Il90b2tlbiI7czo0MDoiaGkyTXM5Nk1paVBxTm9reTJyVUVyNmhGR3o3M01ndTVRMm9HWGdTUiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9mYXZvcml0ZXMvY291bnQvdG90YWwiO3M6NToicm91dGUiO3M6MTU6ImZhdm9yaXRlcy5jb3VudCI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==	1784434095
\.


--
-- TOC entry 5441 (class 0 OID 150438)
-- Dependencies: 259
-- Data for Name: settings; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.settings (id, key, value, type, "group", label, description, "order", created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5460 (class 0 OID 150630)
-- Dependencies: 278
-- Data for Name: site_configurations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.site_configurations (id, hero_badge, hero_title_line1, hero_title_line2, hero_subtitle, hero_images, hero_image_paths, featured_badge, featured_title, featured_properties, support_whatsapp, support_instagram, support_phone, support_email, footer_text, created_at, updated_at) FROM stdin;
1	Exclusividad & Confort	El Arte de	Vivir Bien	Descubre una curaduría exclusiva de propiedades de lujo en las mejores zonas de Venezuela.	["http:\\/\\/127.0.0.1:8000\\/storage\\/hero-images\\/AWESZn6TvDdZB5leW9Bo5VuTrWd5AhmyENJ2Lc7h.jpg","http:\\/\\/127.0.0.1:8000\\/storage\\/hero-images\\/2g2uzs44O3UQvNnT2yiFYG3Kilr6KdOhejQHIBaD.jpg"]	\N	Colección Exclusiva	Propiedades Destacadas	[]	58XXXXXXXXX	msoinmobiliaria	\N	\N	© 2026 MSO Inmobiliaria. Todos los derechos reservados.	2026-07-19 04:06:50	2026-07-19 04:07:38
\.


--
-- TOC entry 5417 (class 0 OID 150061)
-- Dependencies: 235
-- Data for Name: states; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.states (id, country_id, name, created_at, updated_at) FROM stdin;
1	1	Distrito Capital	2026-07-19 04:05:49	2026-07-19 04:05:49
2	1	Miranda	2026-07-19 04:05:49	2026-07-19 04:05:49
3	1	Carabobo	2026-07-19 04:05:49	2026-07-19 04:05:49
\.


--
-- TOC entry 5445 (class 0 OID 150474)
-- Dependencies: 263
-- Data for Name: user_notifications; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.user_notifications (id, user_id, title, message, type, url, data, read_at, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5404 (class 0 OID 149924)
-- Dependencies: 222
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, name, last_name, email, email_verified_at, password, remember_token, phone, profile_photo, bio, specialization, social_links, id_type, id_number, country_id, state_id, municipality_id, parish_id, city_id, address, security_questions, security_answer_1, security_answer_2, security_answer_3, security_questions_set_at, is_active, is_online, last_seen_at, created_at, updated_at, deleted_at) FROM stdin;
1	Carlos	Rodríguez	superadmin@mso.com	2026-07-19 04:05:49	$2y$12$ZmuoZ8LAKHHuLDVhSmeciuEkGJGm97VRB2NtrVGq.34.crXj.Co1i	\N	+58 412 1234567	\N	Super Administrador de MSO Grupo Inmobiliario.	Dirección General	\N	V	12345678	1	1	1	1	1	\N	["\\u00bfCu\\u00e1l es el nombre de tu mejor amigo de la infancia?","\\u00bfCu\\u00e1l es el nombre de tu hijo\\/a?","\\u00bfCu\\u00e1l es el nombre de tu mejor amigo?"]	$2y$12$tBCJhWL0IXUKuA1YfN5BtuphbR49hLeWRlwviLd3ti8UorJXkh7rC	$2y$12$L6VzFlDCrI.H9qTEfsB1weLcqnJLmxinu5nsdisZijDu5XuZ8LIfK	$2y$12$vK9DokFWMsZne7xcPs6QFuuPJ7XhhLR0FHfZTKP/ccifK2ykWnhwm	2026-07-19 04:05:50	t	f	\N	2026-07-19 04:05:50	2026-07-19 04:05:50	\N
2	María	González	admin@mso.com	2026-07-19 04:05:51	$2y$12$Oy/.FSGRdTycxJtlBif.a.LoKyGtBDC.wMoHXvA0O4/siys58oO1u	\N	+58 414 2345678	\N	Administradora con más de 5 años de experiencia en el sector inmobiliario.	Gestión de Propiedades	\N	V	23456789	1	2	2	3	3	\N	["\\u00bfEn qu\\u00e9 a\\u00f1o te graduaste de la escuela?","\\u00bfCu\\u00e1l es el nombre de tu mejor amigo?","\\u00bfCu\\u00e1l es el nombre de tu primera escuela?"]	$2y$12$TZrq89BRpXX2SBYIqVh5xexv784.W5IjTHGZ9epjK6yy74EUzTZpS	$2y$12$Mti9GleBiiM8PplI4Q2TxO60HtQoWy79UuO2c0A3pH2RUDJr7fBbu	$2y$12$gGUlehSCVDVlP3kzhD3sKOmGO8VfTh07JehxvTL8ybraVb9s1Aovu	2026-07-19 04:05:51	t	f	\N	2026-07-19 04:05:51	2026-07-19 04:05:51	\N
3	Luis	Martínez	asesor1@mso.com	2026-07-19 04:05:52	$2y$12$FTfSK5UHDK9pXtNyNSdVkeqp1XkLRSazqNi7RBVlCo0yulWtYYo2C	\N	+58 416 3456789	\N	Asesor inmobiliario especializado en propiedades residenciales de lujo.	Propiedades de Lujo	"{\\"whatsapp\\":\\"584163456789\\",\\"instagram\\":\\"luis.martinez.inmobiliaria\\",\\"facebook\\":\\"luismartinez.asesor\\"}"	V	34567890	1	3	3	4	4	\N	["\\u00bfCu\\u00e1l es el modelo de tu primer auto?","\\u00bfCu\\u00e1l es el t\\u00edtulo de tu libro favorito?","\\u00bfCu\\u00e1l es el nombre de tu abuelo favorito?"]	$2y$12$oW/8wHRouN2lGOi3zp9/Du6Ll3Kjea7enyc0jScC/UCls4eROr/xq	$2y$12$aXnAuQmy.kC6.a9CDbMj9uB835nvTDNe7UqN4FHV4fi.hPdNEciki	$2y$12$V4KD2IoURZLUrR1je9QzqeMYpgDIFASuWMYy3O6V.rOAJi0PVzIc2	2026-07-19 04:05:53	t	f	\N	2026-07-19 04:05:53	2026-07-19 04:05:53	\N
4	Ana Lucía	Fernández	asesor2@mso.com	2026-07-19 04:05:53	$2y$12$IkbMEwHWUI7QIYBcFOJ0XuTcCltcgSlxQeRTc83JBO4gVwFWM15MC	\N	+58 412 9876543	\N	Especialista en alquileres comerciales y oficinas.	Inmuebles Comerciales	"{\\"whatsapp\\":\\"584129876543\\",\\"instagram\\":\\"ana.fernandez.inmuebles\\",\\"linkedin\\":\\"ana-fernandez-inmobiliaria\\"}"	V	45678901	1	1	1	2	2	\N	["\\u00bfEn qu\\u00e9 ciudad naciste?","\\u00bfCu\\u00e1l es el apellido de soltera de tu madre?","\\u00bfCu\\u00e1l es tu comida favorita?"]	$2y$12$/OS2c9Q9ESx1olDsHqprsulFLWD0/0WVBBrdct9hJrS7m3pShRd3y	$2y$12$TD7yPcCihkiHjLOh6lLx3ud141eGhcs6uupyomgA9.v1xHaZbiLsu	$2y$12$8jqvnSP7S0vxRj9fzPGA9ONqDFYbqAPXP9slYTmXJpZaLlmCoji.C	2026-07-19 04:05:54	t	f	\N	2026-07-19 04:05:54	2026-07-19 04:05:54	\N
5	Roberto	Sánchez	auditor@mso.com	2026-07-19 04:05:54	$2y$12$VQ898BYm.RPUijhsqdUPLO0jrqHz2uVq/ZVfSKFYTz9EEIgxzQsWu	\N	+58 414 5678901	\N	Auditor financiero especializado en el sector inmobiliario.	Auditoría y Control	\N	V	56789012	1	2	2	3	3	\N	["\\u00bfCu\\u00e1l es el nombre de tu profesor favorito?","\\u00bfCu\\u00e1l es el modelo de tu primer auto?","\\u00bfCu\\u00e1l es el nombre de tu abuelo favorito?"]	$2y$12$GlZopNc01fXY03xBLIf4t.zdSRXL50ruyFgsLqY8Ans9hjP08gM0m	$2y$12$SJbYDn0v767ko3u6Aitti.vAkE1TgAKsqcYbcnblSO181IutNkFYi	$2y$12$Yxm6pRpdOQkUrdJKWvareOrFZjR8QQYI2XG344PJxpkY2KjemVJMG	2026-07-19 04:05:55	t	f	\N	2026-07-19 04:05:55	2026-07-19 04:05:55	\N
6	Pedro	Pérez	pedro@test.com	2026-07-19 04:05:55	$2y$12$c43vRyw99dQwHY.p2fQWFOltN2EzqE5kL3LPx2l9SUb3isgrqeWoW	\N	04141234567	\N	\N	\N	\N	V	15975346	1	1	1	1	1	\N	["\\u00bfCu\\u00e1l es tu comida favorita?","\\u00bfCu\\u00e1l es el modelo de tu primer auto?","\\u00bfCu\\u00e1l es el nombre de tu padre?"]	$2y$12$k/R50UBzR1phSW79ozvimOY.NApLBWHeUjrWVBUlhQ0He2rYaqyhG	$2y$12$gWvHXL1WfnSNlZr/mXayXOR.coiej6rBVh7ORQynUytAWjaciRKs6	$2y$12$N.mh1wx5OuFjNhrWa8xUz.64btEf7mtEfpfQViTgqNyjUnD9MWb3y	2026-07-19 04:05:56	t	f	\N	2026-07-19 04:05:56	2026-07-19 04:05:56	\N
7	María	Rodríguez	maria.cliente@test.com	2026-07-19 04:05:56	$2y$12$Cx562BX.9.WlBAQDLouTE.QzuVI/h1Tu4uiIyRJDA2tuiqtODPyoW	\N	04241234567	\N	\N	\N	\N	V	26789456	1	3	3	4	4	\N	["\\u00bfCu\\u00e1l es el nombre de tu primera escuela?","\\u00bfCu\\u00e1l es el nombre de tu primer amor?","\\u00bfCu\\u00e1l es el nombre de tu mejor amigo de la infancia?"]	$2y$12$zJfojlODVvKVbcqsgr.4X..A2AI8VhjuNRMIG5KDt.A7Efnasnpem	$2y$12$yDaASBZEBg5QtSiEh7kCUOj/pR8e1pu.l3krR/x6nKJUNk/2R17c6	$2y$12$aVogAbGNxABv7eRlCXsJ3O2Cc2xu91JQ/j6qJkC0rzcEm8FZ/fR4S	2026-07-19 04:05:57	t	f	\N	2026-07-19 04:05:57	2026-07-19 04:05:57	\N
\.


--
-- TOC entry 5503 (class 0 OID 0)
-- Dependencies: 273
-- Name: account_reactivation_tokens_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.account_reactivation_tokens_id_seq', 1, false);


--
-- TOC entry 5504 (class 0 OID 0)
-- Dependencies: 279
-- Name: appointment_settings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.appointment_settings_id_seq', 1, false);


--
-- TOC entry 5505 (class 0 OID 0)
-- Dependencies: 248
-- Name: appointments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.appointments_id_seq', 1, false);


--
-- TOC entry 5506 (class 0 OID 0)
-- Dependencies: 260
-- Name: audit_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.audit_logs_id_seq', 3, true);


--
-- TOC entry 5507 (class 0 OID 0)
-- Dependencies: 242
-- Name: categories_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.categories_id_seq', 1, false);


--
-- TOC entry 5508 (class 0 OID 0)
-- Dependencies: 240
-- Name: cities_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.cities_id_seq', 4, true);


--
-- TOC entry 5509 (class 0 OID 0)
-- Dependencies: 254
-- Name: conversations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.conversations_id_seq', 1, false);


--
-- TOC entry 5510 (class 0 OID 0)
-- Dependencies: 232
-- Name: countries_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.countries_id_seq', 1, true);


--
-- TOC entry 5511 (class 0 OID 0)
-- Dependencies: 230
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- TOC entry 5512 (class 0 OID 0)
-- Dependencies: 250
-- Name: favorites_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.favorites_id_seq', 1, false);


--
-- TOC entry 5513 (class 0 OID 0)
-- Dependencies: 227
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- TOC entry 5514 (class 0 OID 0)
-- Dependencies: 252
-- Name: leads_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.leads_id_seq', 1, false);


--
-- TOC entry 5515 (class 0 OID 0)
-- Dependencies: 256
-- Name: messages_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.messages_id_seq', 1, false);


--
-- TOC entry 5516 (class 0 OID 0)
-- Dependencies: 219
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 289, true);


--
-- TOC entry 5517 (class 0 OID 0)
-- Dependencies: 236
-- Name: municipalities_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.municipalities_id_seq', 3, true);


--
-- TOC entry 5518 (class 0 OID 0)
-- Dependencies: 238
-- Name: parishes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.parishes_id_seq', 4, true);


--
-- TOC entry 5519 (class 0 OID 0)
-- Dependencies: 266
-- Name: permissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.permissions_id_seq', 51, true);


--
-- TOC entry 5520 (class 0 OID 0)
-- Dependencies: 264
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.personal_access_tokens_id_seq', 1, false);


--
-- TOC entry 5521 (class 0 OID 0)
-- Dependencies: 244
-- Name: properties_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.properties_id_seq', 1, false);


--
-- TOC entry 5522 (class 0 OID 0)
-- Dependencies: 246
-- Name: property_images_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.property_images_id_seq', 1, false);


--
-- TOC entry 5523 (class 0 OID 0)
-- Dependencies: 275
-- Name: report_snapshots_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.report_snapshots_id_seq', 1, false);


--
-- TOC entry 5524 (class 0 OID 0)
-- Dependencies: 268
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.roles_id_seq', 5, true);


--
-- TOC entry 5525 (class 0 OID 0)
-- Dependencies: 283
-- Name: service_galleries_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.service_galleries_id_seq', 1, false);


--
-- TOC entry 5526 (class 0 OID 0)
-- Dependencies: 281
-- Name: services_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.services_id_seq', 1, false);


--
-- TOC entry 5527 (class 0 OID 0)
-- Dependencies: 258
-- Name: settings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.settings_id_seq', 1, false);


--
-- TOC entry 5528 (class 0 OID 0)
-- Dependencies: 277
-- Name: site_configurations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.site_configurations_id_seq', 1, true);


--
-- TOC entry 5529 (class 0 OID 0)
-- Dependencies: 234
-- Name: states_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.states_id_seq', 3, true);


--
-- TOC entry 5530 (class 0 OID 0)
-- Dependencies: 262
-- Name: user_notifications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.user_notifications_id_seq', 1, false);


--
-- TOC entry 5531 (class 0 OID 0)
-- Dependencies: 221
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 7, true);


--
-- TOC entry 5192 (class 2606 OID 150596)
-- Name: account_reactivation_tokens account_reactivation_tokens_email_token_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.account_reactivation_tokens
    ADD CONSTRAINT account_reactivation_tokens_email_token_unique UNIQUE (email, token);


--
-- TOC entry 5194 (class 2606 OID 150594)
-- Name: account_reactivation_tokens account_reactivation_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.account_reactivation_tokens
    ADD CONSTRAINT account_reactivation_tokens_pkey PRIMARY KEY (id);


--
-- TOC entry 5204 (class 2606 OID 150673)
-- Name: appointment_settings appointment_settings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.appointment_settings
    ADD CONSTRAINT appointment_settings_pkey PRIMARY KEY (id);


--
-- TOC entry 5105 (class 2606 OID 150271)
-- Name: appointments appointments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.appointments
    ADD CONSTRAINT appointments_pkey PRIMARY KEY (id);


--
-- TOC entry 5163 (class 2606 OID 150465)
-- Name: audit_logs audit_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT audit_logs_pkey PRIMARY KEY (id);


--
-- TOC entry 5043 (class 2606 OID 150000)
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- TOC entry 5040 (class 2606 OID 149989)
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- TOC entry 5064 (class 2606 OID 150135)
-- Name: categories categories_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categories
    ADD CONSTRAINT categories_pkey PRIMARY KEY (id);


--
-- TOC entry 5066 (class 2606 OID 150137)
-- Name: categories categories_slug_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categories
    ADD CONSTRAINT categories_slug_unique UNIQUE (slug);


--
-- TOC entry 5062 (class 2606 OID 150114)
-- Name: cities cities_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cities
    ADD CONSTRAINT cities_pkey PRIMARY KEY (id);


--
-- TOC entry 5138 (class 2606 OID 150397)
-- Name: conversations conversations_client_id_asesor_id_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT conversations_client_id_asesor_id_unique UNIQUE (client_id, asesor_id);


--
-- TOC entry 5145 (class 2606 OID 150385)
-- Name: conversations conversations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT conversations_pkey PRIMARY KEY (id);


--
-- TOC entry 5054 (class 2606 OID 150059)
-- Name: countries countries_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.countries
    ADD CONSTRAINT countries_pkey PRIMARY KEY (id);


--
-- TOC entry 5050 (class 2606 OID 150048)
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- TOC entry 5052 (class 2606 OID 150050)
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- TOC entry 5114 (class 2606 OID 150307)
-- Name: favorites favorites_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.favorites
    ADD CONSTRAINT favorites_pkey PRIMARY KEY (id);


--
-- TOC entry 5120 (class 2606 OID 150319)
-- Name: favorites favorites_user_id_property_id_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.favorites
    ADD CONSTRAINT favorites_user_id_property_id_unique UNIQUE (user_id, property_id);


--
-- TOC entry 5210 (class 2606 OID 150684)
-- Name: appointment_settings idx_appointment_settings_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.appointment_settings
    ADD CONSTRAINT idx_appointment_settings_user_id_unique UNIQUE (user_id);


--
-- TOC entry 5048 (class 2606 OID 150031)
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- TOC entry 5045 (class 2606 OID 150016)
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- TOC entry 5129 (class 2606 OID 150346)
-- Name: leads leads_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leads
    ADD CONSTRAINT leads_pkey PRIMARY KEY (id);


--
-- TOC entry 5152 (class 2606 OID 150419)
-- Name: messages messages_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.messages
    ADD CONSTRAINT messages_pkey PRIMARY KEY (id);


--
-- TOC entry 5010 (class 2606 OID 143378)
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- TOC entry 5184 (class 2606 OID 150553)
-- Name: model_has_permissions model_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_pkey PRIMARY KEY (permission_id, model_id, model_type);


--
-- TOC entry 5187 (class 2606 OID 150567)
-- Name: model_has_roles model_has_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_pkey PRIMARY KEY (role_id, model_id, model_type);


--
-- TOC entry 5058 (class 2606 OID 150084)
-- Name: municipalities municipalities_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.municipalities
    ADD CONSTRAINT municipalities_pkey PRIMARY KEY (id);


--
-- TOC entry 5060 (class 2606 OID 150099)
-- Name: parishes parishes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.parishes
    ADD CONSTRAINT parishes_pkey PRIMARY KEY (id);


--
-- TOC entry 5033 (class 2606 OID 149967)
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- TOC entry 5175 (class 2606 OID 150525)
-- Name: permissions permissions_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_name_guard_name_unique UNIQUE (name, guard_name);


--
-- TOC entry 5177 (class 2606 OID 150523)
-- Name: permissions permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (id);


--
-- TOC entry 5170 (class 2606 OID 150507)
-- Name: personal_access_tokens personal_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_pkey PRIMARY KEY (id);


--
-- TOC entry 5172 (class 2606 OID 150510)
-- Name: personal_access_tokens personal_access_tokens_token_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_token_unique UNIQUE (token);


--
-- TOC entry 5082 (class 2606 OID 150165)
-- Name: properties properties_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.properties
    ADD CONSTRAINT properties_pkey PRIMARY KEY (id);


--
-- TOC entry 5096 (class 2606 OID 150241)
-- Name: property_images property_images_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.property_images
    ADD CONSTRAINT property_images_pkey PRIMARY KEY (id);


--
-- TOC entry 5197 (class 2606 OID 150617)
-- Name: report_snapshots report_snapshots_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.report_snapshots
    ADD CONSTRAINT report_snapshots_pkey PRIMARY KEY (id);


--
-- TOC entry 5200 (class 2606 OID 150624)
-- Name: report_snapshots report_snapshots_user_id_report_type_period_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.report_snapshots
    ADD CONSTRAINT report_snapshots_user_id_report_type_period_unique UNIQUE (user_id, report_type, period);


--
-- TOC entry 5189 (class 2606 OID 150584)
-- Name: role_has_permissions role_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_pkey PRIMARY KEY (permission_id, role_id);


--
-- TOC entry 5179 (class 2606 OID 150539)
-- Name: roles roles_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_name_guard_name_unique UNIQUE (name, guard_name);


--
-- TOC entry 5181 (class 2606 OID 150537)
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- TOC entry 5220 (class 2606 OID 150726)
-- Name: service_galleries service_galleries_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.service_galleries
    ADD CONSTRAINT service_galleries_pkey PRIMARY KEY (id);


--
-- TOC entry 5216 (class 2606 OID 150708)
-- Name: services services_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.services
    ADD CONSTRAINT services_pkey PRIMARY KEY (id);


--
-- TOC entry 5218 (class 2606 OID 150710)
-- Name: services services_slug_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.services
    ADD CONSTRAINT services_slug_unique UNIQUE (slug);


--
-- TOC entry 5036 (class 2606 OID 149977)
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- TOC entry 5156 (class 2606 OID 150455)
-- Name: settings settings_key_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT settings_key_unique UNIQUE (key);


--
-- TOC entry 5158 (class 2606 OID 150453)
-- Name: settings settings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT settings_pkey PRIMARY KEY (id);


--
-- TOC entry 5202 (class 2606 OID 150650)
-- Name: site_configurations site_configurations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.site_configurations
    ADD CONSTRAINT site_configurations_pkey PRIMARY KEY (id);


--
-- TOC entry 5056 (class 2606 OID 150069)
-- Name: states states_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.states
    ADD CONSTRAINT states_pkey PRIMARY KEY (id);


--
-- TOC entry 5166 (class 2606 OID 150487)
-- Name: user_notifications user_notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_notifications
    ADD CONSTRAINT user_notifications_pkey PRIMARY KEY (id);


--
-- TOC entry 5017 (class 2606 OID 149958)
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- TOC entry 5028 (class 2606 OID 149939)
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- TOC entry 5190 (class 1259 OID 150597)
-- Name: account_reactivation_tokens_email_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX account_reactivation_tokens_email_index ON public.account_reactivation_tokens USING btree (email);


--
-- TOC entry 5100 (class 1259 OID 150289)
-- Name: appointments_asesor_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX appointments_asesor_id_index ON public.appointments USING btree (asesor_id);


--
-- TOC entry 5101 (class 1259 OID 150293)
-- Name: appointments_asesor_id_scheduled_date_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX appointments_asesor_id_scheduled_date_index ON public.appointments USING btree (asesor_id, scheduled_date);


--
-- TOC entry 5102 (class 1259 OID 150297)
-- Name: appointments_asesor_id_status_scheduled_date_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX appointments_asesor_id_status_scheduled_date_index ON public.appointments USING btree (asesor_id, status, scheduled_date);


--
-- TOC entry 5103 (class 1259 OID 150292)
-- Name: appointments_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX appointments_created_at_index ON public.appointments USING btree (created_at);


--
-- TOC entry 5106 (class 1259 OID 150288)
-- Name: appointments_property_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX appointments_property_id_index ON public.appointments USING btree (property_id);


--
-- TOC entry 5107 (class 1259 OID 150296)
-- Name: appointments_property_id_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX appointments_property_id_status_index ON public.appointments USING btree (property_id, status);


--
-- TOC entry 5108 (class 1259 OID 150291)
-- Name: appointments_scheduled_date_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX appointments_scheduled_date_index ON public.appointments USING btree (scheduled_date);


--
-- TOC entry 5109 (class 1259 OID 150290)
-- Name: appointments_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX appointments_status_index ON public.appointments USING btree (status);


--
-- TOC entry 5110 (class 1259 OID 150294)
-- Name: appointments_status_scheduled_date_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX appointments_status_scheduled_date_index ON public.appointments USING btree (status, scheduled_date);


--
-- TOC entry 5111 (class 1259 OID 150287)
-- Name: appointments_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX appointments_user_id_index ON public.appointments USING btree (user_id);


--
-- TOC entry 5112 (class 1259 OID 150295)
-- Name: appointments_user_id_scheduled_date_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX appointments_user_id_scheduled_date_index ON public.appointments USING btree (user_id, scheduled_date);


--
-- TOC entry 5159 (class 1259 OID 150732)
-- Name: audit_logs_action_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX audit_logs_action_index ON public.audit_logs USING btree (action);


--
-- TOC entry 5160 (class 1259 OID 150472)
-- Name: audit_logs_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX audit_logs_created_at_index ON public.audit_logs USING btree (created_at);


--
-- TOC entry 5161 (class 1259 OID 150733)
-- Name: audit_logs_event_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX audit_logs_event_index ON public.audit_logs USING btree (event);


--
-- TOC entry 5164 (class 1259 OID 150734)
-- Name: audit_logs_subject_type_subject_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX audit_logs_subject_type_subject_id_index ON public.audit_logs USING btree (subject_type, subject_id);


--
-- TOC entry 5038 (class 1259 OID 149990)
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- TOC entry 5041 (class 1259 OID 150001)
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- TOC entry 5135 (class 1259 OID 150399)
-- Name: conversations_asesor_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX conversations_asesor_id_index ON public.conversations USING btree (asesor_id);


--
-- TOC entry 5136 (class 1259 OID 150403)
-- Name: conversations_asesor_id_is_active_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX conversations_asesor_id_is_active_index ON public.conversations USING btree (asesor_id, is_active);


--
-- TOC entry 5139 (class 1259 OID 150398)
-- Name: conversations_client_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX conversations_client_id_index ON public.conversations USING btree (client_id);


--
-- TOC entry 5140 (class 1259 OID 150402)
-- Name: conversations_client_id_is_active_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX conversations_client_id_is_active_index ON public.conversations USING btree (client_id, is_active);


--
-- TOC entry 5141 (class 1259 OID 150400)
-- Name: conversations_is_active_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX conversations_is_active_index ON public.conversations USING btree (is_active);


--
-- TOC entry 5142 (class 1259 OID 150401)
-- Name: conversations_last_message_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX conversations_last_message_at_index ON public.conversations USING btree (last_message_at);


--
-- TOC entry 5143 (class 1259 OID 150404)
-- Name: conversations_last_message_at_is_active_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX conversations_last_message_at_is_active_index ON public.conversations USING btree (last_message_at, is_active);


--
-- TOC entry 5115 (class 1259 OID 150323)
-- Name: favorites_property_id_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX favorites_property_id_created_at_index ON public.favorites USING btree (property_id, created_at);


--
-- TOC entry 5116 (class 1259 OID 150321)
-- Name: favorites_property_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX favorites_property_id_index ON public.favorites USING btree (property_id);


--
-- TOC entry 5117 (class 1259 OID 150322)
-- Name: favorites_user_id_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX favorites_user_id_created_at_index ON public.favorites USING btree (user_id, created_at);


--
-- TOC entry 5118 (class 1259 OID 150320)
-- Name: favorites_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX favorites_user_id_index ON public.favorites USING btree (user_id);


--
-- TOC entry 5205 (class 1259 OID 150680)
-- Name: idx_appointment_settings_active_always; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_appointment_settings_active_always ON public.appointment_settings USING btree (is_active, apply_always);


--
-- TOC entry 5206 (class 1259 OID 150688)
-- Name: idx_appointment_settings_apply_always; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_appointment_settings_apply_always ON public.appointment_settings USING btree (apply_always);


--
-- TOC entry 5207 (class 1259 OID 150685)
-- Name: idx_appointment_settings_is_active; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_appointment_settings_is_active ON public.appointment_settings USING btree (is_active);


--
-- TOC entry 5208 (class 1259 OID 150679)
-- Name: idx_appointment_settings_user_active; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_appointment_settings_user_active ON public.appointment_settings USING btree (user_id, is_active);


--
-- TOC entry 5211 (class 1259 OID 150682)
-- Name: idx_appointment_settings_user_valid; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_appointment_settings_user_valid ON public.appointment_settings USING btree (user_id, valid_from, valid_to);


--
-- TOC entry 5212 (class 1259 OID 150686)
-- Name: idx_appointment_settings_valid_from; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_appointment_settings_valid_from ON public.appointment_settings USING btree (valid_from);


--
-- TOC entry 5213 (class 1259 OID 150681)
-- Name: idx_appointment_settings_valid_range; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_appointment_settings_valid_range ON public.appointment_settings USING btree (valid_from, valid_to);


--
-- TOC entry 5214 (class 1259 OID 150687)
-- Name: idx_appointment_settings_valid_to; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_appointment_settings_valid_to ON public.appointment_settings USING btree (valid_to);


--
-- TOC entry 5046 (class 1259 OID 150017)
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- TOC entry 5121 (class 1259 OID 150372)
-- Name: leads_asesor_id_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX leads_asesor_id_created_at_index ON public.leads USING btree (asesor_id, created_at);


--
-- TOC entry 5122 (class 1259 OID 150365)
-- Name: leads_asesor_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX leads_asesor_id_index ON public.leads USING btree (asesor_id);


--
-- TOC entry 5123 (class 1259 OID 150368)
-- Name: leads_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX leads_created_at_index ON public.leads USING btree (created_at);


--
-- TOC entry 5124 (class 1259 OID 150362)
-- Name: leads_email_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX leads_email_index ON public.leads USING btree (email);


--
-- TOC entry 5125 (class 1259 OID 150370)
-- Name: leads_email_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX leads_email_status_index ON public.leads USING btree (email, status);


--
-- TOC entry 5126 (class 1259 OID 150363)
-- Name: leads_phone_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX leads_phone_index ON public.leads USING btree (phone);


--
-- TOC entry 5127 (class 1259 OID 150371)
-- Name: leads_phone_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX leads_phone_status_index ON public.leads USING btree (phone, status);


--
-- TOC entry 5130 (class 1259 OID 150367)
-- Name: leads_property_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX leads_property_id_index ON public.leads USING btree (property_id);


--
-- TOC entry 5131 (class 1259 OID 150369)
-- Name: leads_status_asesor_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX leads_status_asesor_id_index ON public.leads USING btree (status, asesor_id);


--
-- TOC entry 5132 (class 1259 OID 150373)
-- Name: leads_status_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX leads_status_created_at_index ON public.leads USING btree (status, created_at);


--
-- TOC entry 5133 (class 1259 OID 150364)
-- Name: leads_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX leads_status_index ON public.leads USING btree (status);


--
-- TOC entry 5134 (class 1259 OID 150366)
-- Name: leads_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX leads_user_id_index ON public.leads USING btree (user_id);


--
-- TOC entry 5146 (class 1259 OID 150436)
-- Name: messages_conversation_id_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX messages_conversation_id_created_at_index ON public.messages USING btree (conversation_id, created_at);


--
-- TOC entry 5147 (class 1259 OID 150430)
-- Name: messages_conversation_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX messages_conversation_id_index ON public.messages USING btree (conversation_id);


--
-- TOC entry 5148 (class 1259 OID 150434)
-- Name: messages_conversation_id_is_read_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX messages_conversation_id_is_read_index ON public.messages USING btree (conversation_id, is_read);


--
-- TOC entry 5149 (class 1259 OID 150433)
-- Name: messages_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX messages_created_at_index ON public.messages USING btree (created_at);


--
-- TOC entry 5150 (class 1259 OID 150432)
-- Name: messages_is_read_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX messages_is_read_index ON public.messages USING btree (is_read);


--
-- TOC entry 5153 (class 1259 OID 150431)
-- Name: messages_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX messages_user_id_index ON public.messages USING btree (user_id);


--
-- TOC entry 5154 (class 1259 OID 150435)
-- Name: messages_user_id_is_read_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX messages_user_id_is_read_index ON public.messages USING btree (user_id, is_read);


--
-- TOC entry 5182 (class 1259 OID 150546)
-- Name: model_has_permissions_model_id_model_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX model_has_permissions_model_id_model_type_index ON public.model_has_permissions USING btree (model_id, model_type);


--
-- TOC entry 5185 (class 1259 OID 150560)
-- Name: model_has_roles_model_id_model_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX model_has_roles_model_id_model_type_index ON public.model_has_roles USING btree (model_id, model_type);


--
-- TOC entry 5168 (class 1259 OID 150511)
-- Name: personal_access_tokens_expires_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX personal_access_tokens_expires_at_index ON public.personal_access_tokens USING btree (expires_at);


--
-- TOC entry 5173 (class 1259 OID 150508)
-- Name: personal_access_tokens_tokenable_type_tokenable_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON public.personal_access_tokens USING btree (tokenable_type, tokenable_id);


--
-- TOC entry 5067 (class 1259 OID 150225)
-- Name: properties_address_location_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_address_location_index ON public.properties USING btree (address, location);


--
-- TOC entry 5068 (class 1259 OID 150202)
-- Name: properties_category_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_category_id_index ON public.properties USING btree (category_id);


--
-- TOC entry 5069 (class 1259 OID 150221)
-- Name: properties_category_id_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_category_id_status_index ON public.properties USING btree (category_id, status);


--
-- TOC entry 5070 (class 1259 OID 150213)
-- Name: properties_city_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_city_id_index ON public.properties USING btree (city_id);


--
-- TOC entry 5071 (class 1259 OID 150222)
-- Name: properties_city_id_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_city_id_status_index ON public.properties USING btree (city_id, status);


--
-- TOC entry 5072 (class 1259 OID 150211)
-- Name: properties_country_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_country_id_index ON public.properties USING btree (country_id);


--
-- TOC entry 5073 (class 1259 OID 150216)
-- Name: properties_country_id_state_id_municipality_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_country_id_state_id_municipality_id_index ON public.properties USING btree (country_id, state_id, municipality_id);


--
-- TOC entry 5074 (class 1259 OID 150209)
-- Name: properties_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_created_at_index ON public.properties USING btree (created_at);


--
-- TOC entry 5075 (class 1259 OID 150218)
-- Name: properties_created_at_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_created_at_status_index ON public.properties USING btree (created_at, status);


--
-- TOC entry 5076 (class 1259 OID 150210)
-- Name: properties_deleted_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_deleted_at_index ON public.properties USING btree (deleted_at);


--
-- TOC entry 5077 (class 1259 OID 150208)
-- Name: properties_featured_until_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_featured_until_index ON public.properties USING btree (featured_until);


--
-- TOC entry 5078 (class 1259 OID 150217)
-- Name: properties_is_featured_featured_until_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_is_featured_featured_until_index ON public.properties USING btree (is_featured, featured_until);


--
-- TOC entry 5079 (class 1259 OID 150207)
-- Name: properties_is_featured_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_is_featured_index ON public.properties USING btree (is_featured);


--
-- TOC entry 5080 (class 1259 OID 150214)
-- Name: properties_municipality_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_municipality_id_index ON public.properties USING btree (municipality_id);


--
-- TOC entry 5083 (class 1259 OID 150203)
-- Name: properties_price_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_price_index ON public.properties USING btree (price);


--
-- TOC entry 5084 (class 1259 OID 150212)
-- Name: properties_state_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_state_id_index ON public.properties USING btree (state_id);


--
-- TOC entry 5085 (class 1259 OID 150223)
-- Name: properties_state_id_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_state_id_status_index ON public.properties USING btree (state_id, status);


--
-- TOC entry 5086 (class 1259 OID 150204)
-- Name: properties_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_status_index ON public.properties USING btree (status);


--
-- TOC entry 5087 (class 1259 OID 150219)
-- Name: properties_status_type_price_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_status_type_price_created_at_index ON public.properties USING btree (status, type, price, created_at);


--
-- TOC entry 5088 (class 1259 OID 150215)
-- Name: properties_status_type_price_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_status_type_price_index ON public.properties USING btree (status, type, price);


--
-- TOC entry 5089 (class 1259 OID 150224)
-- Name: properties_title_description_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_title_description_index ON public.properties USING btree (title, description);


--
-- TOC entry 5090 (class 1259 OID 150205)
-- Name: properties_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_type_index ON public.properties USING btree (type);


--
-- TOC entry 5091 (class 1259 OID 150201)
-- Name: properties_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_user_id_index ON public.properties USING btree (user_id);


--
-- TOC entry 5092 (class 1259 OID 150220)
-- Name: properties_user_id_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_user_id_status_index ON public.properties USING btree (user_id, status);


--
-- TOC entry 5093 (class 1259 OID 150206)
-- Name: properties_views_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX properties_views_index ON public.properties USING btree (views);


--
-- TOC entry 5094 (class 1259 OID 150248)
-- Name: property_images_is_primary_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX property_images_is_primary_index ON public.property_images USING btree (is_primary);


--
-- TOC entry 5097 (class 1259 OID 150247)
-- Name: property_images_property_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX property_images_property_id_index ON public.property_images USING btree (property_id);


--
-- TOC entry 5098 (class 1259 OID 150249)
-- Name: property_images_property_id_is_primary_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX property_images_property_id_is_primary_index ON public.property_images USING btree (property_id, is_primary);


--
-- TOC entry 5099 (class 1259 OID 150250)
-- Name: property_images_property_id_order_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX property_images_property_id_order_index ON public.property_images USING btree (property_id, "order");


--
-- TOC entry 5195 (class 1259 OID 150626)
-- Name: report_snapshots_period_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX report_snapshots_period_index ON public.report_snapshots USING btree (period);


--
-- TOC entry 5198 (class 1259 OID 150625)
-- Name: report_snapshots_report_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX report_snapshots_report_type_index ON public.report_snapshots USING btree (report_type);


--
-- TOC entry 5034 (class 1259 OID 149979)
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- TOC entry 5037 (class 1259 OID 149978)
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- TOC entry 5167 (class 1259 OID 150493)
-- Name: user_notifications_user_id_read_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX user_notifications_user_id_read_at_index ON public.user_notifications USING btree (user_id, read_at);


--
-- TOC entry 5011 (class 1259 OID 149952)
-- Name: users_country_id_state_id_city_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_country_id_state_id_city_id_index ON public.users USING btree (country_id, state_id, city_id);


--
-- TOC entry 5012 (class 1259 OID 149945)
-- Name: users_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_created_at_index ON public.users USING btree (created_at);


--
-- TOC entry 5013 (class 1259 OID 149946)
-- Name: users_deleted_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_deleted_at_index ON public.users USING btree (deleted_at);


--
-- TOC entry 5014 (class 1259 OID 149940)
-- Name: users_email_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_email_index ON public.users USING btree (email);


--
-- TOC entry 5015 (class 1259 OID 149955)
-- Name: users_email_is_active_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_email_is_active_index ON public.users USING btree (email, is_active);


--
-- TOC entry 5018 (class 1259 OID 149947)
-- Name: users_id_number_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_id_number_index ON public.users USING btree (id_number);


--
-- TOC entry 5019 (class 1259 OID 149954)
-- Name: users_id_type_id_number_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_id_type_id_number_index ON public.users USING btree (id_type, id_number);


--
-- TOC entry 5020 (class 1259 OID 149953)
-- Name: users_is_active_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_is_active_created_at_index ON public.users USING btree (is_active, created_at);


--
-- TOC entry 5021 (class 1259 OID 149942)
-- Name: users_is_active_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_is_active_index ON public.users USING btree (is_active);


--
-- TOC entry 5022 (class 1259 OID 149943)
-- Name: users_is_online_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_is_online_index ON public.users USING btree (is_online);


--
-- TOC entry 5023 (class 1259 OID 149944)
-- Name: users_last_seen_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_last_seen_at_index ON public.users USING btree (last_seen_at);


--
-- TOC entry 5024 (class 1259 OID 149951)
-- Name: users_name_last_name_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_name_last_name_index ON public.users USING btree (name, last_name);


--
-- TOC entry 5025 (class 1259 OID 149941)
-- Name: users_phone_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_phone_index ON public.users USING btree (phone);


--
-- TOC entry 5026 (class 1259 OID 149956)
-- Name: users_phone_is_active_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_phone_is_active_index ON public.users USING btree (phone, is_active);


--
-- TOC entry 5029 (class 1259 OID 149948)
-- Name: users_security_answer_1_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_security_answer_1_index ON public.users USING btree (security_answer_1);


--
-- TOC entry 5030 (class 1259 OID 149949)
-- Name: users_security_answer_2_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_security_answer_2_index ON public.users USING btree (security_answer_2);


--
-- TOC entry 5031 (class 1259 OID 149950)
-- Name: users_security_answer_3_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_security_answer_3_index ON public.users USING btree (security_answer_3);


--
-- TOC entry 5233 (class 2606 OID 150282)
-- Name: appointments appointments_asesor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.appointments
    ADD CONSTRAINT appointments_asesor_id_foreign FOREIGN KEY (asesor_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5234 (class 2606 OID 150277)
-- Name: appointments appointments_property_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.appointments
    ADD CONSTRAINT appointments_property_id_foreign FOREIGN KEY (property_id) REFERENCES public.properties(id) ON DELETE CASCADE;


--
-- TOC entry 5235 (class 2606 OID 150272)
-- Name: appointments appointments_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.appointments
    ADD CONSTRAINT appointments_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5245 (class 2606 OID 150466)
-- Name: audit_logs audit_logs_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT audit_logs_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 5224 (class 2606 OID 150115)
-- Name: cities cities_parish_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cities
    ADD CONSTRAINT cities_parish_id_foreign FOREIGN KEY (parish_id) REFERENCES public.parishes(id) ON DELETE CASCADE;


--
-- TOC entry 5241 (class 2606 OID 150391)
-- Name: conversations conversations_asesor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT conversations_asesor_id_foreign FOREIGN KEY (asesor_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5242 (class 2606 OID 150386)
-- Name: conversations conversations_client_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT conversations_client_id_foreign FOREIGN KEY (client_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5236 (class 2606 OID 150313)
-- Name: favorites favorites_property_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.favorites
    ADD CONSTRAINT favorites_property_id_foreign FOREIGN KEY (property_id) REFERENCES public.properties(id) ON DELETE CASCADE;


--
-- TOC entry 5237 (class 2606 OID 150308)
-- Name: favorites favorites_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.favorites
    ADD CONSTRAINT favorites_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5252 (class 2606 OID 150674)
-- Name: appointment_settings idx_appointment_settings_user_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.appointment_settings
    ADD CONSTRAINT idx_appointment_settings_user_id FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5238 (class 2606 OID 150357)
-- Name: leads leads_asesor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leads
    ADD CONSTRAINT leads_asesor_id_foreign FOREIGN KEY (asesor_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 5239 (class 2606 OID 150352)
-- Name: leads leads_property_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leads
    ADD CONSTRAINT leads_property_id_foreign FOREIGN KEY (property_id) REFERENCES public.properties(id) ON DELETE SET NULL;


--
-- TOC entry 5240 (class 2606 OID 150347)
-- Name: leads leads_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leads
    ADD CONSTRAINT leads_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 5243 (class 2606 OID 150420)
-- Name: messages messages_conversation_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.messages
    ADD CONSTRAINT messages_conversation_id_foreign FOREIGN KEY (conversation_id) REFERENCES public.conversations(id) ON DELETE CASCADE;


--
-- TOC entry 5244 (class 2606 OID 150425)
-- Name: messages messages_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.messages
    ADD CONSTRAINT messages_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5247 (class 2606 OID 150547)
-- Name: model_has_permissions model_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- TOC entry 5248 (class 2606 OID 150561)
-- Name: model_has_roles model_has_roles_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- TOC entry 5222 (class 2606 OID 150085)
-- Name: municipalities municipalities_state_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.municipalities
    ADD CONSTRAINT municipalities_state_id_foreign FOREIGN KEY (state_id) REFERENCES public.states(id) ON DELETE CASCADE;


--
-- TOC entry 5223 (class 2606 OID 150100)
-- Name: parishes parishes_municipality_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.parishes
    ADD CONSTRAINT parishes_municipality_id_foreign FOREIGN KEY (municipality_id) REFERENCES public.municipalities(id) ON DELETE CASCADE;


--
-- TOC entry 5225 (class 2606 OID 150196)
-- Name: properties properties_category_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.properties
    ADD CONSTRAINT properties_category_id_foreign FOREIGN KEY (category_id) REFERENCES public.categories(id) ON DELETE SET NULL;


--
-- TOC entry 5226 (class 2606 OID 150186)
-- Name: properties properties_city_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.properties
    ADD CONSTRAINT properties_city_id_foreign FOREIGN KEY (city_id) REFERENCES public.cities(id) ON DELETE CASCADE;


--
-- TOC entry 5227 (class 2606 OID 150166)
-- Name: properties properties_country_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.properties
    ADD CONSTRAINT properties_country_id_foreign FOREIGN KEY (country_id) REFERENCES public.countries(id) ON DELETE CASCADE;


--
-- TOC entry 5228 (class 2606 OID 150176)
-- Name: properties properties_municipality_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.properties
    ADD CONSTRAINT properties_municipality_id_foreign FOREIGN KEY (municipality_id) REFERENCES public.municipalities(id) ON DELETE CASCADE;


--
-- TOC entry 5229 (class 2606 OID 150181)
-- Name: properties properties_parish_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.properties
    ADD CONSTRAINT properties_parish_id_foreign FOREIGN KEY (parish_id) REFERENCES public.parishes(id) ON DELETE CASCADE;


--
-- TOC entry 5230 (class 2606 OID 150171)
-- Name: properties properties_state_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.properties
    ADD CONSTRAINT properties_state_id_foreign FOREIGN KEY (state_id) REFERENCES public.states(id) ON DELETE CASCADE;


--
-- TOC entry 5231 (class 2606 OID 150191)
-- Name: properties properties_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.properties
    ADD CONSTRAINT properties_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5232 (class 2606 OID 150242)
-- Name: property_images property_images_property_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.property_images
    ADD CONSTRAINT property_images_property_id_foreign FOREIGN KEY (property_id) REFERENCES public.properties(id) ON DELETE CASCADE;


--
-- TOC entry 5251 (class 2606 OID 150618)
-- Name: report_snapshots report_snapshots_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.report_snapshots
    ADD CONSTRAINT report_snapshots_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 5249 (class 2606 OID 150573)
-- Name: role_has_permissions role_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- TOC entry 5250 (class 2606 OID 150578)
-- Name: role_has_permissions role_has_permissions_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- TOC entry 5253 (class 2606 OID 150727)
-- Name: service_galleries service_galleries_service_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.service_galleries
    ADD CONSTRAINT service_galleries_service_id_foreign FOREIGN KEY (service_id) REFERENCES public.services(id) ON DELETE CASCADE;


--
-- TOC entry 5221 (class 2606 OID 150070)
-- Name: states states_country_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.states
    ADD CONSTRAINT states_country_id_foreign FOREIGN KEY (country_id) REFERENCES public.countries(id) ON DELETE CASCADE;


--
-- TOC entry 5246 (class 2606 OID 150488)
-- Name: user_notifications user_notifications_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_notifications
    ADD CONSTRAINT user_notifications_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


-- Completed on 2026-07-19 00:18:58

--
-- PostgreSQL database dump complete
--

\unrestrict 4UfLCRgY9cewlrr1C2P0nocKmm5eblGMkp8EwIBCjqR73R5qPxOTVW6D9pqftMf

