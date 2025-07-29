--
-- PostgreSQL database dump
--

-- Dumped from database version 14.18 (Ubuntu 14.18-0ubuntu0.22.04.1)
-- Dumped by pg_dump version 16.4 (Ubuntu 16.4-1.pgdg22.04+1)

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
-- Name: public; Type: SCHEMA; Schema: -; Owner: postgres
--

-- *not* creating schema, since initdb creates it


ALTER SCHEMA public OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: arrondissements; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.arrondissements (
    id bigint NOT NULL,
    code character varying(255) NOT NULL,
    nom text NOT NULL,
    slug character varying(255) NOT NULL,
    "communeId" bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.arrondissements OWNER TO postgres;

--
-- Name: arrondissements_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.arrondissements_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.arrondissements_id_seq OWNER TO postgres;

--
-- Name: arrondissements_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.arrondissements_id_seq OWNED BY public.arrondissements.id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO postgres;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO postgres;

--
-- Name: categories_critere; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.categories_critere (
    id bigint NOT NULL,
    type text NOT NULL,
    slug text NOT NULL,
    is_mandatory boolean DEFAULT false NOT NULL,
    criteres_ajustable json,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.categories_critere OWNER TO postgres;

--
-- Name: categories_critere_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.categories_critere_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.categories_critere_id_seq OWNER TO postgres;

--
-- Name: categories_critere_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.categories_critere_id_seq OWNED BY public.categories_critere.id;


--
-- Name: categories_document; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.categories_document (
    id bigint NOT NULL,
    nom text NOT NULL,
    slug character varying(255) NOT NULL,
    description text,
    format character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.categories_document OWNER TO postgres;

--
-- Name: categories_document_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.categories_document_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.categories_document_id_seq OWNER TO postgres;

--
-- Name: categories_document_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.categories_document_id_seq OWNED BY public.categories_document.id;


--
-- Name: categories_projet; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.categories_projet (
    id bigint NOT NULL,
    categorie text NOT NULL,
    slug character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.categories_projet OWNER TO postgres;

--
-- Name: categories_projet_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.categories_projet_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.categories_projet_id_seq OWNER TO postgres;

--
-- Name: categories_projet_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.categories_projet_id_seq OWNED BY public.categories_projet.id;


--
-- Name: champs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.champs (
    id bigint NOT NULL,
    label text NOT NULL,
    info text,
    attribut character varying(255) NOT NULL,
    placeholder text,
    is_required boolean NOT NULL,
    default_value character varying(255),
    "isEvaluated" boolean DEFAULT false NOT NULL,
    ordre_affichage integer DEFAULT 0 NOT NULL,
    type_champ character varying(255) DEFAULT 'text'::character varying NOT NULL,
    "sectionId" bigint,
    "documentId" bigint,
    meta_options jsonb,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    champ_standard boolean DEFAULT false NOT NULL,
    CONSTRAINT champs_type_champ_check CHECK (((type_champ)::text = ANY ((ARRAY['text'::character varying, 'textarea'::character varying, 'number'::character varying, 'date'::character varying, 'boolean'::character varying, 'select'::character varying, 'multiselect'::character varying, 'file'::character varying, 'geolocation'::character varying, 'rating'::character varying, 'number_input'::character varying, 'currency_input'::character varying, 'phone_number_input'::character varying, 'radio'::character varying, 'radio_rating'::character varying, 'slider_number'::character varying, 'multiselect_checkbox'::character varying, 'multiselect_file'::character varying])::text[])))
);


ALTER TABLE public.champs OWNER TO postgres;

--
-- Name: champs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.champs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.champs_id_seq OWNER TO postgres;

--
-- Name: champs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.champs_id_seq OWNED BY public.champs.id;


--
-- Name: champs_projet; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.champs_projet (
    id bigint NOT NULL,
    valeur jsonb,
    commentaire text,
    projetable_type character varying(255) NOT NULL,
    projetable_id bigint NOT NULL,
    "champId" bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.champs_projet OWNER TO postgres;

--
-- Name: champs_projet_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.champs_projet_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.champs_projet_id_seq OWNER TO postgres;

--
-- Name: champs_projet_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.champs_projet_id_seq OWNED BY public.champs_projet.id;


--
-- Name: champs_sections; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.champs_sections (
    id bigint NOT NULL,
    intitule text NOT NULL,
    description text NOT NULL,
    slug character varying(255) NOT NULL,
    ordre_affichage integer DEFAULT 0 NOT NULL,
    type character varying(255) DEFAULT 'formulaire'::character varying NOT NULL,
    "documentId" bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    CONSTRAINT champs_sections_type_check CHECK (((type)::text = ANY ((ARRAY['entete'::character varying, 'formulaire'::character varying, 'table_matiere'::character varying, 'tableau'::character varying])::text[])))
);


ALTER TABLE public.champs_sections OWNER TO postgres;

--
-- Name: champs_sections_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.champs_sections_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.champs_sections_id_seq OWNER TO postgres;

--
-- Name: champs_sections_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.champs_sections_id_seq OWNED BY public.champs_sections.id;


--
-- Name: cibles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cibles (
    id bigint NOT NULL,
    cible text NOT NULL,
    slug character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.cibles OWNER TO postgres;

--
-- Name: cibles_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.cibles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.cibles_id_seq OWNER TO postgres;

--
-- Name: cibles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.cibles_id_seq OWNED BY public.cibles.id;


--
-- Name: cibles_projets; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cibles_projets (
    id bigint NOT NULL,
    "cibleId" bigint,
    projetable_type character varying(255) NOT NULL,
    projetable_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.cibles_projets OWNER TO postgres;

--
-- Name: cibles_projets_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.cibles_projets_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.cibles_projets_id_seq OWNER TO postgres;

--
-- Name: cibles_projets_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.cibles_projets_id_seq OWNED BY public.cibles_projets.id;


--
-- Name: commentaires; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.commentaires (
    id bigint NOT NULL,
    commentaire text NOT NULL,
    date timestamp(0) without time zone,
    commentaireable_type character varying(255) NOT NULL,
    commentaireable_id bigint NOT NULL,
    "commentateurId" bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.commentaires OWNER TO postgres;

--
-- Name: commentaires_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.commentaires_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.commentaires_id_seq OWNER TO postgres;

--
-- Name: commentaires_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.commentaires_id_seq OWNED BY public.commentaires.id;


--
-- Name: communes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.communes (
    id bigint NOT NULL,
    code character varying(255) NOT NULL,
    nom text NOT NULL,
    slug character varying(255) NOT NULL,
    "departementId" bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.communes OWNER TO postgres;

--
-- Name: communes_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.communes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.communes_id_seq OWNER TO postgres;

--
-- Name: communes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.communes_id_seq OWNED BY public.communes.id;


--
-- Name: composants_programme; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.composants_programme (
    id bigint NOT NULL,
    indice integer NOT NULL,
    intitule text NOT NULL,
    slug character varying(255) NOT NULL,
    "typeId" bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.composants_programme OWNER TO postgres;

--
-- Name: composants_programme_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.composants_programme_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.composants_programme_id_seq OWNER TO postgres;

--
-- Name: composants_programme_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.composants_programme_id_seq OWNED BY public.composants_programme.id;


--
-- Name: composants_projet; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.composants_projet (
    id bigint NOT NULL,
    "composantId" bigint,
    projetable_type character varying(255) NOT NULL,
    projetable_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.composants_projet OWNER TO postgres;

--
-- Name: composants_projet_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.composants_projet_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.composants_projet_id_seq OWNER TO postgres;

--
-- Name: composants_projet_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.composants_projet_id_seq OWNED BY public.composants_projet.id;


--
-- Name: criteres; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.criteres (
    id bigint NOT NULL,
    intitule text NOT NULL,
    ponderation double precision DEFAULT '0'::double precision NOT NULL,
    commentaire text,
    is_mandatory boolean DEFAULT false NOT NULL,
    est_general boolean DEFAULT false NOT NULL,
    categorie_critere_id bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.criteres OWNER TO postgres;

--
-- Name: criteres_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.criteres_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.criteres_id_seq OWNER TO postgres;

--
-- Name: criteres_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.criteres_id_seq OWNED BY public.criteres.id;


--
-- Name: decisions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.decisions (
    id bigint NOT NULL,
    valeur character varying(255) NOT NULL,
    date timestamp(0) without time zone NOT NULL,
    observations text,
    "observateurId" bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.decisions OWNER TO postgres;

--
-- Name: decisions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.decisions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.decisions_id_seq OWNER TO postgres;

--
-- Name: decisions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.decisions_id_seq OWNED BY public.decisions.id;


--
-- Name: departements; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.departements (
    id bigint NOT NULL,
    code character varying(255) NOT NULL,
    nom text NOT NULL,
    slug character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.departements OWNER TO postgres;

--
-- Name: departements_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.departements_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.departements_id_seq OWNER TO postgres;

--
-- Name: departements_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.departements_id_seq OWNED BY public.departements.id;


--
-- Name: dgpd; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dgpd (
    id bigint NOT NULL,
    nom text NOT NULL,
    slug character varying(255) NOT NULL,
    description text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.dgpd OWNER TO postgres;

--
-- Name: dgpd_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dgpd_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.dgpd_id_seq OWNER TO postgres;

--
-- Name: dgpd_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dgpd_id_seq OWNED BY public.dgpd.id;


--
-- Name: documents; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.documents (
    id bigint NOT NULL,
    nom text NOT NULL,
    slug character varying(255) NOT NULL,
    description text,
    "categorieId" bigint,
    type character varying(255) DEFAULT 'formulaire'::character varying NOT NULL,
    metadata jsonb,
    structure jsonb,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    CONSTRAINT documents_type_check CHECK (((type)::text = ANY ((ARRAY['document'::character varying, 'formulaire'::character varying, 'grille'::character varying, 'checklist'::character varying])::text[])))
);


ALTER TABLE public.documents OWNER TO postgres;

--
-- Name: documents_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.documents_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.documents_id_seq OWNER TO postgres;

--
-- Name: documents_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.documents_id_seq OWNED BY public.documents.id;


--
-- Name: dpaf; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dpaf (
    id bigint NOT NULL,
    nom text NOT NULL,
    slug character varying(255) NOT NULL,
    description text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.dpaf OWNER TO postgres;

--
-- Name: dpaf_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.dpaf_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.dpaf_id_seq OWNER TO postgres;

--
-- Name: dpaf_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.dpaf_id_seq OWNED BY public.dpaf.id;


--
-- Name: evaluation_champs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.evaluation_champs (
    id bigint NOT NULL,
    note character varying(255) NOT NULL,
    commentaires text,
    date_note timestamp(0) without time zone NOT NULL,
    "evaluationId" bigint NOT NULL,
    "champId" bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.evaluation_champs OWNER TO postgres;

--
-- Name: evaluation_champs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.evaluation_champs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.evaluation_champs_id_seq OWNER TO postgres;

--
-- Name: evaluation_champs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.evaluation_champs_id_seq OWNED BY public.evaluation_champs.id;


--
-- Name: evaluation_criteres; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.evaluation_criteres (
    id bigint NOT NULL,
    note character varying(255) NOT NULL,
    evaluateur_id bigint NOT NULL,
    notation_id bigint NOT NULL,
    critere_id bigint,
    categorie_critere_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.evaluation_criteres OWNER TO postgres;

--
-- Name: evaluation_criteres_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.evaluation_criteres_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.evaluation_criteres_id_seq OWNER TO postgres;

--
-- Name: evaluation_criteres_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.evaluation_criteres_id_seq OWNED BY public.evaluation_criteres.id;


--
-- Name: evaluations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.evaluations (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.evaluations OWNER TO postgres;

--
-- Name: evaluations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.evaluations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.evaluations_id_seq OWNER TO postgres;

--
-- Name: evaluations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.evaluations_id_seq OWNED BY public.evaluations.id;


--
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
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: financements; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.financements (
    id bigint NOT NULL,
    nom text NOT NULL,
    nom_usuel text NOT NULL,
    slug character varying(255) NOT NULL,
    type character varying(255) DEFAULT 'source'::character varying NOT NULL,
    "financementId" bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    CONSTRAINT financements_type_check CHECK (((type)::text = ANY ((ARRAY['type'::character varying, 'nature'::character varying, 'source'::character varying])::text[])))
);


ALTER TABLE public.financements OWNER TO postgres;

--
-- Name: financements_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.financements_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.financements_id_seq OWNER TO postgres;

--
-- Name: financements_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.financements_id_seq OWNED BY public.financements.id;


--
-- Name: groupe_utilisateur_roles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.groupe_utilisateur_roles (
    id bigint NOT NULL,
    "roleId" bigint NOT NULL,
    "groupeUtilisateurId" bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.groupe_utilisateur_roles OWNER TO postgres;

--
-- Name: groupe_utilisateur_roles_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.groupe_utilisateur_roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.groupe_utilisateur_roles_id_seq OWNER TO postgres;

--
-- Name: groupe_utilisateur_roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.groupe_utilisateur_roles_id_seq OWNED BY public.groupe_utilisateur_roles.id;


--
-- Name: groupe_utilisateur_users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.groupe_utilisateur_users (
    id bigint NOT NULL,
    "userId" bigint NOT NULL,
    "groupeUtilisateurId" bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.groupe_utilisateur_users OWNER TO postgres;

--
-- Name: groupe_utilisateur_users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.groupe_utilisateur_users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.groupe_utilisateur_users_id_seq OWNER TO postgres;

--
-- Name: groupe_utilisateur_users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.groupe_utilisateur_users_id_seq OWNED BY public.groupe_utilisateur_users.id;


--
-- Name: groupes_utilisateur; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.groupes_utilisateur (
    id bigint NOT NULL,
    nom text NOT NULL,
    slug character varying(255) NOT NULL,
    description text,
    profilable_type character varying(255),
    profilable_id bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.groupes_utilisateur OWNER TO postgres;

--
-- Name: groupes_utilisateur_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.groupes_utilisateur_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.groupes_utilisateur_id_seq OWNER TO postgres;

--
-- Name: groupes_utilisateur_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.groupes_utilisateur_id_seq OWNED BY public.groupes_utilisateur.id;


--
-- Name: idees_projet; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.idees_projet (
    id bigint NOT NULL,
    "secteurId" bigint,
    "ministereId" bigint,
    "categorieId" bigint,
    "responsableId" bigint,
    "demandeurId" bigint,
    identifiant_bip character varying(255),
    identifiant_sigfp character varying(255),
    est_coherent boolean DEFAULT false NOT NULL,
    statut character varying(255) DEFAULT '00_brouillon'::character varying NOT NULL,
    phase character varying(255) DEFAULT 'identification'::character varying NOT NULL,
    sous_phase character varying(255) DEFAULT 'redaction'::character varying NOT NULL,
    decision json,
    titre_projet character varying(255) NOT NULL,
    sigle character varying(255),
    type_projet character varying(255) DEFAULT 'simple'::character varying NOT NULL,
    duree character varying(255),
    origine text,
    fondement text,
    situation_actuelle text,
    situation_desiree text,
    contraintes text,
    description_projet text,
    echeancier text,
    description_extrants text,
    caracteristiques text,
    impact_environnement text,
    aspect_organisationnel text,
    risques_immediats text,
    conclusions text,
    description text,
    description_decision text,
    estimation_couts text,
    public_cible text,
    constats_majeurs text,
    objectif_general text,
    sommaire text,
    score_climatique numeric(8,2) DEFAULT '0'::numeric NOT NULL,
    score_amc numeric(8,2) DEFAULT '0'::numeric NOT NULL,
    cout_dollar_americain numeric(15,2),
    cout_euro numeric(15,2),
    cout_dollar_canadien numeric(15,2),
    date_debut_etude timestamp(0) without time zone,
    date_fin_etude timestamp(0) without time zone,
    cout_estimatif_projet json,
    "ficheIdee" json NOT NULL,
    parties_prenantes json,
    objectifs_specifiques json,
    resultats_attendus json,
    body_projet json NOT NULL,
    isdeleted boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    est_soumise boolean DEFAULT false NOT NULL,
    CONSTRAINT idees_projet_phase_check CHECK (((phase)::text = ANY ((ARRAY['identification'::character varying, 'evaluation_ex_tante'::character varying, 'selection'::character varying])::text[]))),
    CONSTRAINT idees_projet_sous_phase_check CHECK (((sous_phase)::text = ANY ((ARRAY['redaction'::character varying, 'analyse_idee'::character varying, 'etude_de_profil'::character varying, 'etude_de_prefaisabilite'::character varying, 'etude_de_faisabilite'::character varying])::text[]))),
    CONSTRAINT idees_projet_statut_check CHECK (((statut)::text = ANY ((ARRAY['00_brouillon'::character varying, '01_idee_de_projet'::character varying, '02a_analyse'::character varying, '02b_amc'::character varying, '02c_validation'::character varying, '03a_profil'::character varying, '99_abandon'::character varying, '1a_prefaisabilite'::character varying, '10_pret'::character varying, '05a_TDR_faisabilité'::character varying, '04a_R_TDR_Préfaisabilité'::character varying, '05b_Evaluation_TDR_F'::character varying, '05b_SoumissionRapportF'::character varying, '05c_ValidationF'::character varying, '03a_NoteConceptuel'::character varying, '03cx_ValidationNoteAameliorer'::character varying, '03c_R_ValidationNoteAameliorer'::character varying, '03b_EvaluationNote'::character varying, '03c_ValidationProfil'::character varying, '03c_R_ValidationProfilNoteAameliorer'::character varying, '04a_TDR_Prefaisabilité'::character varying])::text[]))),
    CONSTRAINT idees_projet_type_projet_check CHECK (((type_projet)::text = ANY ((ARRAY['simple'::character varying, 'complexe1'::character varying, 'complex2'::character varying])::text[])))
);


ALTER TABLE public.idees_projet OWNER TO postgres;

--
-- Name: idees_projet_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.idees_projet_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.idees_projet_id_seq OWNER TO postgres;

--
-- Name: idees_projet_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.idees_projet_id_seq OWNED BY public.idees_projet.id;


--
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
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: lieux_intervention_projets; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.lieux_intervention_projets (
    id bigint NOT NULL,
    "departementId" bigint NOT NULL,
    "communeId" bigint NOT NULL,
    "arrondissementId" bigint,
    "villageId" bigint,
    projetable_type character varying(255) NOT NULL,
    projetable_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.lieux_intervention_projets OWNER TO postgres;

--
-- Name: lieux_intervention_projets_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.lieux_intervention_projets_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.lieux_intervention_projets_id_seq OWNER TO postgres;

--
-- Name: lieux_intervention_projets_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.lieux_intervention_projets_id_seq OWNED BY public.lieux_intervention_projets.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
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
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: notations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.notations (
    id bigint NOT NULL,
    libelle character varying(255) NOT NULL,
    valeur character varying(255) NOT NULL,
    commentaire text,
    critere_id bigint,
    categorie_critere_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.notations OWNER TO postgres;

--
-- Name: notations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.notations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.notations_id_seq OWNER TO postgres;

--
-- Name: notations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.notations_id_seq OWNED BY public.notations.id;


--
-- Name: oauth_access_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.oauth_access_tokens (
    id character(80) NOT NULL,
    user_id bigint,
    client_id uuid NOT NULL,
    name character varying(255),
    scopes text,
    revoked boolean NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    expires_at timestamp(0) without time zone
);


ALTER TABLE public.oauth_access_tokens OWNER TO postgres;

--
-- Name: oauth_auth_codes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.oauth_auth_codes (
    id character(80) NOT NULL,
    user_id bigint NOT NULL,
    client_id uuid NOT NULL,
    scopes text,
    revoked boolean NOT NULL,
    expires_at timestamp(0) without time zone
);


ALTER TABLE public.oauth_auth_codes OWNER TO postgres;

--
-- Name: oauth_clients; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.oauth_clients (
    id uuid NOT NULL,
    owner_type character varying(255),
    owner_id bigint,
    name character varying(255) NOT NULL,
    secret character varying(255),
    provider character varying(255),
    redirect_uris text NOT NULL,
    grant_types text NOT NULL,
    revoked boolean NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.oauth_clients OWNER TO postgres;

--
-- Name: oauth_device_codes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.oauth_device_codes (
    id character(80) NOT NULL,
    user_id bigint,
    client_id uuid NOT NULL,
    user_code character(8) NOT NULL,
    scopes text NOT NULL,
    revoked boolean NOT NULL,
    user_approved_at timestamp(0) without time zone,
    last_polled_at timestamp(0) without time zone,
    expires_at timestamp(0) without time zone
);


ALTER TABLE public.oauth_device_codes OWNER TO postgres;

--
-- Name: oauth_refresh_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.oauth_refresh_tokens (
    id character(80) NOT NULL,
    access_token_id character(80) NOT NULL,
    revoked boolean NOT NULL,
    expires_at timestamp(0) without time zone
);


ALTER TABLE public.oauth_refresh_tokens OWNER TO postgres;

--
-- Name: odds; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.odds (
    id bigint NOT NULL,
    odd text NOT NULL,
    slug character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.odds OWNER TO postgres;

--
-- Name: odds_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.odds_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.odds_id_seq OWNER TO postgres;

--
-- Name: odds_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.odds_id_seq OWNED BY public.odds.id;


--
-- Name: odds_projets; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.odds_projets (
    id bigint NOT NULL,
    "oddId" bigint,
    projetable_type character varying(255) NOT NULL,
    projetable_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.odds_projets OWNER TO postgres;

--
-- Name: odds_projets_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.odds_projets_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.odds_projets_id_seq OWNER TO postgres;

--
-- Name: odds_projets_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.odds_projets_id_seq OWNED BY public.odds_projets.id;


--
-- Name: organisations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.organisations (
    id bigint NOT NULL,
    nom text NOT NULL,
    slug character varying(255) NOT NULL,
    description text,
    type character varying(255) DEFAULT 'etatique'::character varying NOT NULL,
    "parentId" bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    CONSTRAINT organisations_type_check CHECK (((type)::text = ANY ((ARRAY['ministere'::character varying, 'dpaf'::character varying, 'dgpd'::character varying, 'dgb'::character varying, 'etatique'::character varying, 'partenaire'::character varying])::text[])))
);


ALTER TABLE public.organisations OWNER TO postgres;

--
-- Name: organisations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.organisations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.organisations_id_seq OWNER TO postgres;

--
-- Name: organisations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.organisations_id_seq OWNED BY public.organisations.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO postgres;

--
-- Name: permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.permissions (
    id bigint NOT NULL,
    nom character varying(255) NOT NULL,
    slug character varying(255) NOT NULL,
    description text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.permissions OWNER TO postgres;

--
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
-- Name: permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.permissions_id_seq OWNED BY public.permissions.id;


--
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
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.personal_access_tokens_id_seq OWNED BY public.personal_access_tokens.id;


--
-- Name: personnes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.personnes (
    id bigint NOT NULL,
    nom character varying(255) NOT NULL,
    prenom character varying(255) NOT NULL,
    poste character varying(255),
    "organismeId" bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.personnes OWNER TO postgres;

--
-- Name: personnes_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.personnes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.personnes_id_seq OWNER TO postgres;

--
-- Name: personnes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.personnes_id_seq OWNED BY public.personnes.id;


--
-- Name: projets; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.projets (
    id bigint NOT NULL,
    "ideeProjetId" bigint NOT NULL,
    "secteurId" bigint NOT NULL,
    "ministereId" bigint NOT NULL,
    "categorieId" bigint NOT NULL,
    "responsableId" bigint NOT NULL,
    "demandeurId" bigint NOT NULL,
    identifiant_bip character varying(255),
    identifiant_sigfp character varying(255),
    statut character varying(255) DEFAULT '00_brouillon'::character varying NOT NULL,
    phase character varying(255) DEFAULT 'identification'::character varying NOT NULL,
    sous_phase character varying(255) DEFAULT 'redaction'::character varying NOT NULL,
    decision json,
    titre_projet character varying(255) NOT NULL,
    sigle character varying(255) NOT NULL,
    type_projet character varying(255) DEFAULT 'simple'::character varying NOT NULL,
    origine text,
    fondement text,
    situation_actuelle text,
    situation_desiree text,
    contraintes text,
    description_projet text,
    echeancier text,
    description_extrants text,
    caracteristiques text,
    impact_environnement text,
    aspect_organisationnel text,
    risques_immediats text,
    conclusions text,
    description text,
    description_decision text,
    estimation_couts text,
    public_cible text,
    constats_majeurs text,
    objectif_general text,
    sommaire text,
    score_climatique numeric(8,2) DEFAULT '0'::numeric NOT NULL,
    score_amc numeric(8,2) DEFAULT '0'::numeric NOT NULL,
    cout_dollar_americain numeric(15,2),
    cout_euro numeric(15,2),
    cout_dollar_canadien numeric(15,2),
    date_debut_etude timestamp(0) without time zone,
    date_fin_etude timestamp(0) without time zone,
    date_prevue_demarrage timestamp(0) without time zone,
    date_effective_demarrage timestamp(0) without time zone,
    duree json,
    cout_estimatif_projet json,
    "ficheIdee" json NOT NULL,
    parties_prenantes json,
    objectifs_specifiques json,
    resultats_attendus json,
    body_projet json NOT NULL,
    isdeleted boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    CONSTRAINT projets_phase_check CHECK (((phase)::text = ANY ((ARRAY['identification'::character varying, 'evaluation_ex_tante'::character varying, 'selection'::character varying])::text[]))),
    CONSTRAINT projets_sous_phase_check CHECK (((sous_phase)::text = ANY ((ARRAY['redaction'::character varying, 'analyse_idee'::character varying, 'etude_de_profil'::character varying, 'etude_de_prefaisabilite'::character varying, 'etude_de_faisabilite'::character varying])::text[]))),
    CONSTRAINT projets_statut_check CHECK (((statut)::text = ANY ((ARRAY['00_brouillon'::character varying, '01_idee_de_projet'::character varying, '02a_analyse'::character varying, '02b_amc'::character varying, '02c_validation'::character varying, '03a_profil'::character varying, '99_abandon'::character varying, '1a_prefaisabilite'::character varying, '10_pret'::character varying, '05a_TDR_faisabilité'::character varying, '04a_R_TDR_Préfaisabilité'::character varying, '05b_Evaluation_TDR_F'::character varying, '05b_SoumissionRapportF'::character varying, '05c_ValidationF'::character varying, '03a_NoteConceptuel'::character varying, '03cx_ValidationNoteAameliorer'::character varying, '03c_R_ValidationNoteAameliorer'::character varying, '03b_EvaluationNote'::character varying, '03c_ValidationProfil'::character varying, '03c_R_ValidationProfilNoteAameliorer'::character varying, '04a_TDR_Prefaisabilité'::character varying])::text[]))),
    CONSTRAINT projets_type_projet_check CHECK (((type_projet)::text = ANY ((ARRAY['simple'::character varying, 'complexe1'::character varying, 'complex2'::character varying])::text[])))
);


ALTER TABLE public.projets OWNER TO postgres;

--
-- Name: projets_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.projets_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.projets_id_seq OWNER TO postgres;

--
-- Name: projets_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.projets_id_seq OWNED BY public.projets.id;


--
-- Name: role_permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.role_permissions (
    id bigint NOT NULL,
    "roleId" bigint NOT NULL,
    "permissionId" bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.role_permissions OWNER TO postgres;

--
-- Name: role_permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.role_permissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.role_permissions_id_seq OWNER TO postgres;

--
-- Name: role_permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.role_permissions_id_seq OWNED BY public.role_permissions.id;


--
-- Name: roles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.roles (
    id bigint NOT NULL,
    nom character varying(255) NOT NULL,
    slug character varying(255) NOT NULL,
    description text,
    roleable_type character varying(255),
    roleable_id bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.roles OWNER TO postgres;

--
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
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


--
-- Name: secteurs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.secteurs (
    id bigint NOT NULL,
    nom text NOT NULL,
    slug character varying(255) NOT NULL,
    description text,
    type character varying(255) DEFAULT 'secteur'::character varying NOT NULL,
    "secteurId" bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    CONSTRAINT secteurs_type_check CHECK (((type)::text = ANY ((ARRAY['grand-secteur'::character varying, 'secteur'::character varying, 'sous-secteur'::character varying])::text[])))
);


ALTER TABLE public.secteurs OWNER TO postgres;

--
-- Name: secteurs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.secteurs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.secteurs_id_seq OWNER TO postgres;

--
-- Name: secteurs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.secteurs_id_seq OWNED BY public.secteurs.id;


--
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
-- Name: sources_financement_projets; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sources_financement_projets (
    id bigint NOT NULL,
    "sourceId" bigint NOT NULL,
    projetable_type character varying(255) NOT NULL,
    projetable_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.sources_financement_projets OWNER TO postgres;

--
-- Name: sources_financement_projets_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.sources_financement_projets_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.sources_financement_projets_id_seq OWNER TO postgres;

--
-- Name: sources_financement_projets_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.sources_financement_projets_id_seq OWNED BY public.sources_financement_projets.id;


--
-- Name: statuts; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.statuts (
    id bigint NOT NULL,
    statut text NOT NULL,
    date timestamp(0) without time zone,
    statutable_type character varying(255) NOT NULL,
    statutable_id bigint NOT NULL,
    avis text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.statuts OWNER TO postgres;

--
-- Name: statuts_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.statuts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.statuts_id_seq OWNER TO postgres;

--
-- Name: statuts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.statuts_id_seq OWNED BY public.statuts.id;


--
-- Name: track_infos; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.track_infos (
    id bigint NOT NULL,
    update_data jsonb,
    track_info_type character varying(255) NOT NULL,
    track_info_id bigint NOT NULL,
    description text,
    "createdAt" timestamp(0) without time zone NOT NULL,
    "createdBy" bigint,
    "updatedAt" timestamp(0) without time zone,
    "updateBy" bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.track_infos OWNER TO postgres;

--
-- Name: track_infos_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.track_infos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.track_infos_id_seq OWNER TO postgres;

--
-- Name: track_infos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.track_infos_id_seq OWNED BY public.track_infos.id;


--
-- Name: types_intervention; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.types_intervention (
    id bigint NOT NULL,
    type_intervention text NOT NULL,
    "secteurId" bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.types_intervention OWNER TO postgres;

--
-- Name: types_intervention_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.types_intervention_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.types_intervention_id_seq OWNER TO postgres;

--
-- Name: types_intervention_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.types_intervention_id_seq OWNED BY public.types_intervention.id;


--
-- Name: types_intervention_projets; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.types_intervention_projets (
    id bigint NOT NULL,
    "typeId" bigint NOT NULL,
    projetable_type character varying(255) NOT NULL,
    projetable_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.types_intervention_projets OWNER TO postgres;

--
-- Name: types_intervention_projets_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.types_intervention_projets_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.types_intervention_projets_id_seq OWNER TO postgres;

--
-- Name: types_intervention_projets_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.types_intervention_projets_id_seq OWNED BY public.types_intervention_projets.id;


--
-- Name: types_programme; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.types_programme (
    id bigint NOT NULL,
    type_programme text NOT NULL,
    slug character varying(255) NOT NULL,
    "typeId" bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.types_programme OWNER TO postgres;

--
-- Name: types_programme_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.types_programme_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.types_programme_id_seq OWNER TO postgres;

--
-- Name: types_programme_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.types_programme_id_seq OWNED BY public.types_programme.id;


--
-- Name: user_roles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.user_roles (
    id bigint NOT NULL,
    "roleId" bigint NOT NULL,
    "userId" bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.user_roles OWNER TO postgres;

--
-- Name: user_roles_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.user_roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.user_roles_id_seq OWNER TO postgres;

--
-- Name: user_roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.user_roles_id_seq OWNED BY public.user_roles.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    provider character varying(255) DEFAULT 'keycloack'::character varying NOT NULL,
    provider_user_id character varying(255) NOT NULL,
    username character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    status character varying(255) DEFAULT 'actif'::character varying NOT NULL,
    is_email_verified boolean DEFAULT false NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    "personneId" bigint NOT NULL,
    "roleId" bigint,
    last_connection timestamp(0) without time zone,
    ip_address character varying(255),
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    settings json,
    person json,
    keycloak_id character varying(255),
    type character varying(255),
    "lastRequest" timestamp(0) without time zone,
    profilable_id bigint,
    profilable_type character varying(255),
    account_verification_request_sent_at timestamp(0) without time zone,
    password_update_at timestamp(0) without time zone,
    last_password_remember character varying(255),
    token character varying(255),
    link_is_valide boolean DEFAULT false NOT NULL,
    CONSTRAINT users_status_check CHECK (((status)::text = ANY ((ARRAY['actif'::character varying, 'suspendu'::character varying, 'invité'::character varying])::text[])))
);


ALTER TABLE public.users OWNER TO postgres;

--
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
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: villages; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.villages (
    id bigint NOT NULL,
    code character varying(255) NOT NULL,
    nom text NOT NULL,
    slug character varying(255) NOT NULL,
    "arrondissementId" bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.villages OWNER TO postgres;

--
-- Name: villages_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.villages_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.villages_id_seq OWNER TO postgres;

--
-- Name: villages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.villages_id_seq OWNED BY public.villages.id;


--
-- Name: workflows; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.workflows (
    id bigint NOT NULL,
    statut character varying(255) DEFAULT '00_brouillon'::character varying NOT NULL,
    phase character varying(255) DEFAULT 'identification'::character varying NOT NULL,
    sous_phase character varying(255) DEFAULT 'redaction'::character varying NOT NULL,
    date timestamp(0) without time zone,
    projetable_type character varying(255) NOT NULL,
    projetable_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    CONSTRAINT workflows_phase_check CHECK (((phase)::text = ANY ((ARRAY['identification'::character varying, 'evaluation_ex_tante'::character varying, 'selection'::character varying])::text[]))),
    CONSTRAINT workflows_sous_phase_check CHECK (((sous_phase)::text = ANY ((ARRAY['redaction'::character varying, 'analyse_idee'::character varying, 'etude_de_profil'::character varying, 'etude_de_prefaisabilite'::character varying, 'etude_de_faisabilite'::character varying])::text[]))),
    CONSTRAINT workflows_statut_check CHECK (((statut)::text = ANY ((ARRAY['00_brouillon'::character varying, '01_idee_de_projet'::character varying, '02a_analyse'::character varying, '02b_amc'::character varying, '02c_validation'::character varying, '03a_profil'::character varying, '99_abandon'::character varying, '1a_prefaisabilite'::character varying, '10_pret'::character varying, '05a_TDR_faisabilité'::character varying, '04a_R_TDR_Préfaisabilité'::character varying, '05b_Evaluation_TDR_F'::character varying, '05b_SoumissionRapportF'::character varying, '05c_ValidationF'::character varying, '03a_NoteConceptuel'::character varying, '03cx_ValidationNoteAameliorer'::character varying, '03c_R_ValidationNoteAameliorer'::character varying, '03b_EvaluationNote'::character varying, '03c_ValidationProfil'::character varying, '03c_R_ValidationProfilNoteAameliorer'::character varying, '04a_TDR_Prefaisabilité'::character varying])::text[])))
);


ALTER TABLE public.workflows OWNER TO postgres;

--
-- Name: workflows_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.workflows_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.workflows_id_seq OWNER TO postgres;

--
-- Name: workflows_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.workflows_id_seq OWNED BY public.workflows.id;


--
-- Name: arrondissements id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.arrondissements ALTER COLUMN id SET DEFAULT nextval('public.arrondissements_id_seq'::regclass);


--
-- Name: categories_critere id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categories_critere ALTER COLUMN id SET DEFAULT nextval('public.categories_critere_id_seq'::regclass);


--
-- Name: categories_document id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categories_document ALTER COLUMN id SET DEFAULT nextval('public.categories_document_id_seq'::regclass);


--
-- Name: categories_projet id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categories_projet ALTER COLUMN id SET DEFAULT nextval('public.categories_projet_id_seq'::regclass);


--
-- Name: champs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.champs ALTER COLUMN id SET DEFAULT nextval('public.champs_id_seq'::regclass);


--
-- Name: champs_projet id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.champs_projet ALTER COLUMN id SET DEFAULT nextval('public.champs_projet_id_seq'::regclass);


--
-- Name: champs_sections id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.champs_sections ALTER COLUMN id SET DEFAULT nextval('public.champs_sections_id_seq'::regclass);


--
-- Name: cibles id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cibles ALTER COLUMN id SET DEFAULT nextval('public.cibles_id_seq'::regclass);


--
-- Name: cibles_projets id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cibles_projets ALTER COLUMN id SET DEFAULT nextval('public.cibles_projets_id_seq'::regclass);


--
-- Name: commentaires id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.commentaires ALTER COLUMN id SET DEFAULT nextval('public.commentaires_id_seq'::regclass);


--
-- Name: communes id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.communes ALTER COLUMN id SET DEFAULT nextval('public.communes_id_seq'::regclass);


--
-- Name: composants_programme id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.composants_programme ALTER COLUMN id SET DEFAULT nextval('public.composants_programme_id_seq'::regclass);


--
-- Name: composants_projet id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.composants_projet ALTER COLUMN id SET DEFAULT nextval('public.composants_projet_id_seq'::regclass);


--
-- Name: criteres id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.criteres ALTER COLUMN id SET DEFAULT nextval('public.criteres_id_seq'::regclass);


--
-- Name: decisions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.decisions ALTER COLUMN id SET DEFAULT nextval('public.decisions_id_seq'::regclass);


--
-- Name: departements id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.departements ALTER COLUMN id SET DEFAULT nextval('public.departements_id_seq'::regclass);


--
-- Name: dgpd id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dgpd ALTER COLUMN id SET DEFAULT nextval('public.dgpd_id_seq'::regclass);


--
-- Name: documents id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.documents ALTER COLUMN id SET DEFAULT nextval('public.documents_id_seq'::regclass);


--
-- Name: dpaf id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dpaf ALTER COLUMN id SET DEFAULT nextval('public.dpaf_id_seq'::regclass);


--
-- Name: evaluation_champs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.evaluation_champs ALTER COLUMN id SET DEFAULT nextval('public.evaluation_champs_id_seq'::regclass);


--
-- Name: evaluation_criteres id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.evaluation_criteres ALTER COLUMN id SET DEFAULT nextval('public.evaluation_criteres_id_seq'::regclass);


--
-- Name: evaluations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.evaluations ALTER COLUMN id SET DEFAULT nextval('public.evaluations_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: financements id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.financements ALTER COLUMN id SET DEFAULT nextval('public.financements_id_seq'::regclass);


--
-- Name: groupe_utilisateur_roles id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.groupe_utilisateur_roles ALTER COLUMN id SET DEFAULT nextval('public.groupe_utilisateur_roles_id_seq'::regclass);


--
-- Name: groupe_utilisateur_users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.groupe_utilisateur_users ALTER COLUMN id SET DEFAULT nextval('public.groupe_utilisateur_users_id_seq'::regclass);


--
-- Name: groupes_utilisateur id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.groupes_utilisateur ALTER COLUMN id SET DEFAULT nextval('public.groupes_utilisateur_id_seq'::regclass);


--
-- Name: idees_projet id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.idees_projet ALTER COLUMN id SET DEFAULT nextval('public.idees_projet_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: lieux_intervention_projets id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lieux_intervention_projets ALTER COLUMN id SET DEFAULT nextval('public.lieux_intervention_projets_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: notations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notations ALTER COLUMN id SET DEFAULT nextval('public.notations_id_seq'::regclass);


--
-- Name: odds id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.odds ALTER COLUMN id SET DEFAULT nextval('public.odds_id_seq'::regclass);


--
-- Name: odds_projets id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.odds_projets ALTER COLUMN id SET DEFAULT nextval('public.odds_projets_id_seq'::regclass);


--
-- Name: organisations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.organisations ALTER COLUMN id SET DEFAULT nextval('public.organisations_id_seq'::regclass);


--
-- Name: permissions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions ALTER COLUMN id SET DEFAULT nextval('public.permissions_id_seq'::regclass);


--
-- Name: personal_access_tokens id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens ALTER COLUMN id SET DEFAULT nextval('public.personal_access_tokens_id_seq'::regclass);


--
-- Name: personnes id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personnes ALTER COLUMN id SET DEFAULT nextval('public.personnes_id_seq'::regclass);


--
-- Name: projets id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.projets ALTER COLUMN id SET DEFAULT nextval('public.projets_id_seq'::regclass);


--
-- Name: role_permissions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_permissions ALTER COLUMN id SET DEFAULT nextval('public.role_permissions_id_seq'::regclass);


--
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- Name: secteurs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.secteurs ALTER COLUMN id SET DEFAULT nextval('public.secteurs_id_seq'::regclass);


--
-- Name: sources_financement_projets id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sources_financement_projets ALTER COLUMN id SET DEFAULT nextval('public.sources_financement_projets_id_seq'::regclass);


--
-- Name: statuts id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.statuts ALTER COLUMN id SET DEFAULT nextval('public.statuts_id_seq'::regclass);


--
-- Name: track_infos id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.track_infos ALTER COLUMN id SET DEFAULT nextval('public.track_infos_id_seq'::regclass);


--
-- Name: types_intervention id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.types_intervention ALTER COLUMN id SET DEFAULT nextval('public.types_intervention_id_seq'::regclass);


--
-- Name: types_intervention_projets id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.types_intervention_projets ALTER COLUMN id SET DEFAULT nextval('public.types_intervention_projets_id_seq'::regclass);


--
-- Name: types_programme id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.types_programme ALTER COLUMN id SET DEFAULT nextval('public.types_programme_id_seq'::regclass);


--
-- Name: user_roles id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_roles ALTER COLUMN id SET DEFAULT nextval('public.user_roles_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: villages id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.villages ALTER COLUMN id SET DEFAULT nextval('public.villages_id_seq'::regclass);


--
-- Name: workflows id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.workflows ALTER COLUMN id SET DEFAULT nextval('public.workflows_id_seq'::regclass);


--
-- Data for Name: arrondissements; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.arrondissements (id, code, nom, slug, "communeId", created_at, updated_at, deleted_at) FROM stdin;
1	AL-BAN-01	Banikoara	banikoara	1	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
2	AL-BAN-02	Founougo	founougo	1	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
3	AL-BAN-03	Gomparou	gomparou	1	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
4	AL-BAN-04	Goumori	goumori	1	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
5	AL-BAN-05	Kokey	kokey	1	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
6	AL-BAN-06	Kokiborou	kokiborou	1	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
7	AL-BAN-07	Ounet	ounet	1	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
8	AL-BAN-08	Sompérékou	somperekou	1	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
9	AL-BAN-09	Soroko	soroko	1	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
10	AL-BAN-10	Toura	toura	1	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
11	AL-GOG-01	Bagou	bagou	2	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
12	AL-GOG-02	Gogounou	gogounou	2	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
13	AL-GOG-03	Gounarou	gounarou	2	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
14	AL-GOG-04	Ouara	ouara	2	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
15	AL-GOG-05	Sori	sori	2	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
16	AL-GOG-06	Zoungou-Pantrossi	zoungou-pantrossi	2	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
17	AL-KAN-01	Angaradébou	angaradebou	3	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
18	AL-KAN-02	Bensékou	bensekou	3	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
19	AL-KAN-03	Donwari	donwari	3	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
20	AL-KAN-04	Kandi I	kandi-i	3	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
21	AL-KAN-05	Kandi II	kandi-ii	3	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
22	AL-KAN-06	Kandi III	kandi-iii	3	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
23	AL-KAN-07	Kassakou	kassakou	3	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
24	AL-KAN-08	Saah	saah	3	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
25	AL-KAN-09	Sam	sam	3	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
26	AL-KAN-10	Sonsoro	sonsoro	3	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
27	AL-KAR-01	Birni Lafia	birni-lafia	4	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
28	AL-KAR-02	Bogo-Bogo	bogo-bogo	4	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
29	AL-KAR-03	Karimama	karimama	4	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
30	AL-KAR-04	Kompa	kompa	4	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
31	AL-KAR-05	Monsey	monsey	4	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
32	AL-MAL-01	Garou	garou	5	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
33	AL-MAL-02	Guéné	guene	5	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
34	AL-MAL-03	Malanville	malanville	5	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
35	AL-MAL-04	Madécali	madecali	5	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
36	AL-MAL-05	Toumboutou	toumboutou	5	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
37	AL-SEG-01	Libantè	libante	6	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
38	AL-SEG-02	Liboussou	liboussou	6	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
39	AL-SEG-03	Lougou	lougou	6	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
40	AL-SEG-04	Segbana	segbana	6	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
41	AL-SEG-05	Sokotindji	sokotindji	6	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
42	AK-BOU-01	Boukoumbé	boukoumbe	7	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
43	AK-BOU-02	Dipoli	dipoli	7	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
44	AK-BOU-03	Korontière	korontiere	7	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
45	AK-BOU-04	Kossoucoingou	kossoucoingou	7	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
46	AK-BOU-05	Manta	manta	7	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
47	AK-BOU-06	Natta	natta	7	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
48	AK-BOU-07	Tabota	tabota	7	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
49	AK-COB-01	Cobly	cobly	8	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
50	AK-COB-02	Datori	datori	8	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
51	AK-COB-03	Kountori	kountori	8	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
52	AK-COB-04	Tapoga	tapoga	8	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
53	AK-KER-01	Brignamaro	brignamaro	9	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
54	AK-KER-02	Firou	firou	9	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
55	AK-KER-03	Kérou	kerou	9	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
56	AK-KER-04	Koabagou	koabagou	9	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
57	AK-KOU-01	Birni	birni	10	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
58	AK-KOU-02	Chabi-Couma	chabi-couma	10	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
59	AK-KOU-03	Fô-Tancé	fo-tance	10	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
60	AK-KOU-04	Guilmaro	guilmaro	10	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
61	AK-KOU-05	Kouandé	kouande	10	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
62	AK-KOU-06	Oroukayo	oroukayo	10	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
63	AK-MAT-01	Dassari	dassari	11	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
64	AK-MAT-02	Gouandé	gouande	11	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
65	AK-MAT-03	Matéri	materi	11	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
66	AK-MAT-04	Nodi	nodi	11	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
67	AK-MAT-05	Tantéga	tantega	11	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
68	AK-MAT-06	Tchianhoun-Cossi	tchianhoun-cossi	11	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
69	AK-NAT-01	Kotopounga	kotopounga	12	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
70	AK-NAT-02	Kouaba	kouaba	12	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
71	AK-NAT-03	Koundata	koundata	12	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
72	AK-NAT-04	Natitingou I	natitingou-i	12	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
73	AK-NAT-05	Natitingou II	natitingou-ii	12	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
74	AK-NAT-06	Natitingou III	natitingou-iii	12	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
75	AK-NAT-07	Natitingou IV	natitingou-iv	12	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
76	AK-NAT-08	Perma	perma	12	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
77	AK-NAT-09	Tchoumi-Tchoumi	tchoumi-tchoumi	12	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
78	AK-PEH-01	Gnémasson	gnemasson	13	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
79	AK-PEH-02	Péhunco	pehunco	13	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
80	AK-PEH-03	Tobré	tobre	13	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
81	AK-TAN-01	Cotiakou	cotiakou	14	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
82	AK-TAN-02	N'Dahonta	ndahonta	14	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
83	AK-TAN-03	Taiakou	taiakou	14	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
84	AK-TAN-04	Tanguiéta	tanguieta	14	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
85	AK-TAN-05	Tanongou	tanongou	14	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
86	AK-TOU-01	Kouarfa	kouarfa	15	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
87	AK-TOU-02	Tampégré	tampegre	15	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
88	AK-TOU-03	Toucountouna	toucountouna	15	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
89	AT-ABC-01	Abomey-Calavi	abomey-calavi	16	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
90	AT-ABC-02	Akassato	akassato	16	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
91	AT-ABC-03	Godomey	godomey	16	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
92	AT-ABC-04	Glo-Djigbé	glo-djigbe	16	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
93	AT-ABC-05	Hêvié	hevie	16	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
94	AT-ABC-06	Kpanroun	kpanroun	16	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
95	AT-ABC-07	Ouèdo	ouedo	16	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
96	AT-ABC-08	Togba	togba	16	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
97	AT-ABC-09	Zinvié	zinvie	16	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
98	AT-ALL-01	Agbanou	agbanou	17	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
99	AT-ALL-02	Ahouannonzoun	ahouannonzoun	17	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
100	AT-ALL-03	Allada	allada	17	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
101	AT-ALL-04	Attogon	attogon	17	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
102	AT-ALL-05	Avakpa	avakpa	17	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
103	AT-ALL-06	Ayou	ayou	17	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
104	AT-ALL-07	Hinvi	hinvi	17	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
105	AT-ALL-08	Lissègazoun	lissegazoun	17	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
106	AT-ALL-09	Lon-Agonmey	lon-agonmey	17	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
107	AT-ALL-10	Sekou	sekou	17	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
108	AT-ALL-11	Togoudo	togoudo	17	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
109	AT-ALL-12	Tokpa-Avagoudo	tokpa-avagoudo	17	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
110	AT-KPO-01	Aganmalomè	aganmalome	18	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
111	AT-KPO-02	Agbanto	agbanto	18	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
112	AT-KPO-03	Agonkanmè	agonkanme	18	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
113	AT-KPO-04	Dédomè	dedome	18	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
114	AT-KPO-05	Dékanmè	dekanme	18	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
115	AT-KPO-06	Kpomassè	kpomasse	18	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
116	AT-KPO-07	Sègbèya	segbeya	18	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
117	AT-KPO-08	Sègbohouè	segbohoue	18	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
118	AT-KPO-09	Tokpa-Domè	tokpa-dome	18	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
119	AT-OUI-01	Avlékété	avlekete	19	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
120	AT-OUI-02	Djègbadji	djegbadji	19	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
121	AT-OUI-03	Gakpé	gakpe	19	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
122	AT-OUI-04	Houakpè-Daho	houakpe-daho	19	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
123	AT-OUI-05	Ouidah I	ouidah-i	19	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
124	AT-OUI-06	Ouidah II	ouidah-ii	19	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
125	AT-OUI-07	Ouidah III	ouidah-iii	19	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
126	AT-OUI-08	Ouidah IV	ouidah-iv	19	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
127	AT-OUI-09	Pahou	pahou	19	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
128	AT-OUI-10	Savi	savi	19	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
129	AT-SAV-01	Ahomey-Lokpo	ahomey-lokpo	20	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
130	AT-SAV-02	Dékanmey	dekanmey	20	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
131	AT-SAV-03	Ganvié I	ganvie-i	20	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
132	AT-SAV-04	Ganvié II	ganvie-ii	20	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
133	AT-SAV-05	Houédo-Aguékon	houedo-aguekon	20	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
134	AT-SAV-06	Sô-Ava	so-ava	20	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
135	AT-SAV-07	Vekky	vekky	20	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
136	AT-TOF-01	Agué	ague	21	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
137	AT-TOF-02	Colli-Agbamè	colli-agbame	21	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
138	AT-TOF-03	Coussi	coussi	21	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
139	AT-TOF-04	Damè	dame	21	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
140	AT-TOF-05	Djanglanmè	djanglanme	21	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
141	AT-TOF-06	Houègbo	houegbo	21	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
142	AT-TOF-07	Kpomè	kpome	21	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
143	AT-TOF-08	Sè	se	21	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
144	AT-TOF-09	Sèhouè	sehoue	21	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
145	AT-TOF-10	Toffo-Agué	toffo-ague	21	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
146	AT-TOR-01	Avamè	avame	22	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
147	AT-TOR-02	Azohouè-Aliho	azohoue-aliho	22	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
148	AT-TOR-03	Azohouè-Cada	azohoue-cada	22	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
149	AT-TOR-04	Tori-Bossito	tori-bossito	22	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
150	AT-TOR-05	Tori-Cada	tori-cada	22	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
151	AT-TOR-06	Tori-Gare Tori aïdohoue	tori-gare-tori-aidohoue	22	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
152	AT-TOR-07	Tori acadjamè	tori-acadjame	22	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
153	AT-ZE-01	Adjan	adjan	23	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
154	AT-ZE-02	Dawè	dawe	23	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
155	AT-ZE-03	Djigbé	djigbe	23	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
156	AT-ZE-04	Dodji-Bata	dodji-bata	23	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
157	AT-ZE-05	Hèkanmé	hekanme	23	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
158	AT-ZE-06	Koundokpoé	koundokpoe	23	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
159	AT-ZE-07	Sèdjè-Dénou	sedje-denou	23	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
160	AT-ZE-08	Sèdjè-Houégoudo	sedje-houegoudo	23	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
161	AT-ZE-09	Tangbo-Djevié	tangbo-djevie	23	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
162	AT-ZE-10	Yokpo	yokpo	23	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
163	AT-ZE-11	Zè	ze	23	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
164	BO-BEM-01	Bembéréké	bembereke	24	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
165	BO-BEM-02	Béroubouay	beroubouay	24	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
166	BO-BEM-03	Bouanri	bouanri	24	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
167	BO-BEM-04	Gamia	gamia	24	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
168	BO-BEM-05	Ina	ina	24	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
169	BO-KAL-01	Basso	basso	25	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
170	BO-KAL-02	Bouka	bouka	25	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
171	BO-KAL-03	Dérassi	derassi	25	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
172	BO-KAL-04	Dunkassa	dunkassa	25	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
173	BO-KAL-05	Kalalé	kalale	25	2025-07-28 17:22:20	2025-07-28 17:22:20	\N
174	BO-KAL-06	Péonga	peonga	25	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
175	BO-NDA-01	Bori	bori	26	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
176	BO-NDA-02	Gbégourou	gbegourou	26	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
177	BO-NDA-03	N'Dali	ndali	26	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
178	BO-NDA-04	Ouénou	ouenou	26	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
179	BO-NDA-05	Sirarou	sirarou	26	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
180	BO-NIK-01	Biro	biro	27	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
181	BO-NIK-02	Gnonkourakali	gnonkourakali	27	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
182	BO-NIK-03	Nikki	nikki	27	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
183	BO-NIK-04	Ouénou	ouenou1	27	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
184	BO-NIK-05	Sérékalé	serekale	27	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
185	BO-NIK-06	Suya	suya	27	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
186	BO-NIK-07	Tasso	tasso	27	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
187	BO-PAR-01	1er arrondissement de Parakou	1er-arrondissement-de-parakou	28	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
188	BO-PAR-02	2e arrondissement de Parakou	2e-arrondissement-de-parakou	28	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
189	BO-PAR-03	3e arrondissement de Parakou	3e-arrondissement-de-parakou	28	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
190	BO-PER-01	Gninsy	gninsy	29	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
191	BO-PER-02	Guinagourou	guinagourou	29	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
192	BO-PER-03	Kpané	kpane	29	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
193	BO-PER-04	Pébié	pebie	29	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
194	BO-PER-05	Pèrèrè	perere	29	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
195	BO-PER-06	Sontou	sontou	29	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
196	BO-SIN-01	Fô-Bourè	fo-boure	30	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
197	BO-SIN-02	Sèkèrè	sekere	30	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
198	BO-SIN-03	Sikki	sikki	30	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
199	BO-SIN-04	Sinendé	sinende	30	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
200	BO-TCH-01	Alafiarou	alafiarou	31	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
201	BO-TCH-02	Bétérou	beterou	31	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
202	BO-TCH-03	Goro	goro	31	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
203	BO-TCH-04	Kika	kika	31	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
204	BO-TCH-05	Sanson	sanson	31	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
205	BO-TCH-06	Tchaourou	tchaourou	31	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
206	BO-TCH-07	Tchatchou	tchatchou	31	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
207	CO-BAN-01	Agoua	agoua	32	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
208	CO-BAN-02	Akpassi	akpassi	32	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
209	CO-BAN-03	Atokoligbé	atokoligbe	32	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
210	CO-BAN-04	Bantè	bante	32	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
211	CO-BAN-05	Bobè	bobe	32	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
212	CO-BAN-06	Gouka	gouka	32	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
213	CO-BAN-07	Koko	koko	32	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
214	CO-BAN-08	Lougba	lougba	32	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
215	CO-BAN-09	Pira	pira	32	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
216	CO-DAS-01	Akofodjoulè	akofodjoule	33	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
217	CO-DAS-02	Dassa I	dassa-i	33	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
218	CO-DAS-03	Dassa II	dassa-ii	33	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
219	CO-DAS-04	Gbaffo	gbaffo	33	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
220	CO-DAS-05	Kerè	kere	33	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
221	CO-DAS-06	Kpingni	kpingni	33	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
222	CO-DAS-07	Lèma	lema	33	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
223	CO-DAS-08	Paouignan	paouignan	33	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
224	CO-DAS-09	Soclogbo	soclogbo	33	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
225	CO-DAS-10	Tré	tre	33	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
226	CO-GLA-01	Aklankpa	aklankpa	34	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
227	CO-GLA-02	Assanté	assante	34	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
228	CO-GLA-03	Glazoué	glazoue	34	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
229	CO-GLA-04	Gomè	gome	34	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
230	CO-GLA-05	Kpakpaza	kpakpaza	34	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
231	CO-GLA-06	Magoumi	magoumi	34	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
232	CO-GLA-07	Ouèdèmè	ouedeme	34	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
233	CO-GLA-08	Sokponta	sokponta	34	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
234	CO-GLA-09	Thio	thio	34	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
235	CO-GLA-10	Zaffé	zaffe	34	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
236	CO-OUE-01	Challa-Ogoi	challa-ogoi	35	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
237	CO-OUE-02	Djègbè	djegbe	35	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
238	CO-OUE-03	Gbanlin	gbanlin	35	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
239	CO-OUE-04	Kémon	kemon	35	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
240	CO-OUE-05	Kilibo	kilibo	35	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
241	CO-OUE-06	Laminou	laminou	35	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
242	CO-OUE-07	Odougba	odougba	35	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
243	CO-OUE-08	Ouèssè	ouesse	35	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
244	CO-OUE-09	Toui	toui	35	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
245	CO-SAV-01	Djaloukou	djaloukou	36	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
246	CO-SAV-02	Doumè	doume	36	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
247	CO-SAV-03	Gobada	gobada	36	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
248	CO-SAV-04	Kpataba	kpataba	36	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
249	CO-SAV-05	Lahotan	lahotan	36	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
250	CO-SAV-06	Lèma	lema1	36	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
251	CO-SAV-07	Logozohè	logozohe	36	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
252	CO-SAV-08	Monkpa	monkpa	36	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
253	CO-SAV-09	Ottola	ottola	36	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
254	CO-SAV-10	Ouèssè	ouesse1	36	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
255	CO-SAV-11	Savalou-aga	savalou-aga	36	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
256	CO-SAV-12	Savalou-agbado	savalou-agbado	36	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
257	CO-SAV-13	Savalou-attakè	savalou-attake	36	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
258	CO-SAV-14	Tchetti	tchetti	36	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
259	CO-SAV2-01	Adido	adido	37	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
260	CO-SAV2-02	Bèssè	besse	37	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
261	CO-SAV2-03	Boni	boni	37	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
262	CO-SAV2-04	Kaboua	kaboua	37	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
263	CO-SAV2-05	Ofè	ofe	37	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
264	CO-SAV2-06	Okpara	okpara	37	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
265	CO-SAV2-07	Plateau	plateau	37	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
266	CO-SAV2-08	Sakin	sakin	37	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
267	KO-APL-01	Aplahoué	aplahoue	38	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
268	KO-APL-02	Atomè	atome	38	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
269	KO-APL-03	Azovè	azove	38	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
270	KO-APL-04	Dekpo	dekpo	38	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
271	KO-APL-05	Godohou	godohou	38	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
272	KO-APL-06	Kissamey	kissamey	38	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
273	KO-APL-07	Lonkly	lonkly	38	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
274	KO-DJA-01	Adjintimey	adjintimey	39	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
275	KO-DJA-02	Bètoumey	betoumey	39	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
276	KO-DJA-03	Djakotomey I	djakotomey-i	39	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
277	KO-DJA-04	Djakotomey II	djakotomey-ii	39	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
278	KO-DJA-05	Gohomey	gohomey	39	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
279	KO-DJA-06	Houègamey	houegamey	39	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
280	KO-DJA-07	Kinkinhoué	kinkinhoue	39	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
281	KO-DJA-08	Kokohoué	kokohoue	39	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
282	KO-DJA-09	Kpoba	kpoba	39	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
283	KO-DJA-10	Sokouhoué	sokouhoue	39	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
284	KO-DOG-01	Ayomi	ayomi	40	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
285	KO-DOG-02	Dèvè	deve	40	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
286	KO-DOG-03	Honton	honton	40	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
287	KO-DOG-04	Lokogohoué	lokogohoue	40	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
288	KO-DOG-05	Madjrè	madjre	40	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
289	KO-DOG-06	Tota	tota	40	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
290	KO-DOG-07	Totchagni	totchagni	40	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
291	KO-KLO-01	Adjanhonmè	adjanhonme	41	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
292	KO-KLO-02	Ahogbèya	ahogbeya	41	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
293	KO-KLO-03	Aya-Hohoué	aya-hohoue	41	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
294	KO-KLO-04	Djotto	djotto	41	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
295	KO-KLO-05	Hondji	hondji	41	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
296	KO-KLO-06	Klouékanmè	klouekanme	41	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
297	KO-KLO-07	Lanta	lanta	41	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
298	KO-KLO-08	Tchikpé	tchikpe	41	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
299	KO-LAL-01	Adoukandji	adoukandji	42	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
300	KO-LAL-02	Ahondjinnako	ahondjinnako	42	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
301	KO-LAL-03	Ahomadegbe	ahomadegbe	42	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
302	KO-LAL-04	Banigbé	banigbe	42	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
303	KO-LAL-05	Gnizounmè	gnizounme	42	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
304	KO-LAL-06	Hlassamè	hlassame	42	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
305	KO-LAL-07	Lalo	lalo	42	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
306	KO-LAL-08	Lokogba	lokogba	42	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
307	KO-LAL-09	Tchito	tchito	42	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
308	KO-LAL-10	Tohou	tohou	42	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
309	KO-LAL-11	Zalli	zalli	42	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
310	KO-TOV-01	Adjido	adjido	43	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
311	KO-TOV-02	Avédjin	avedjin	43	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
312	KO-TOV-03	Doko	doko	43	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
313	KO-TOV-04	Houédogli	houedogli	43	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
314	KO-TOV-05	Missinko	missinko	43	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
315	KO-TOV-06	Tannou-Gola	tannou-gola	43	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
316	KO-TOV-07	Toviklin	toviklin	43	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
317	DO-BAS-01	Alédjo	aledjo	44	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
318	DO-BAS-02	Bassila	bassila	44	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
319	DO-BAS-03	Manigri	manigri	44	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
320	DO-BAS-04	Pénéssoulou	penessoulou	44	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
321	DO-COP-01	Anandana	anandana	45	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
322	DO-COP-02	Copargo	copargo	45	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
323	DO-COP-03	Pabégou	pabegou	45	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
324	DO-COP-04	Singré	singre	45	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
325	DO-DJO-01	Barei	barei	46	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
326	DO-DJO-02	Bariénou	barienou	46	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
327	DO-DJO-03	Bélléfoungou	bellefoungou	46	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
328	DO-DJO-04	Bougou	bougou	46	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
329	DO-DJO-05	Djougou I	djougou-i	46	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
330	DO-DJO-06	Djougou II	djougou-ii	46	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
331	DO-DJO-07	Djougou III	djougou-iii	46	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
332	DO-DJO-08	Kolokondé	kolokonde	46	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
333	DO-DJO-09	Onklou	onklou	46	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
334	DO-DJO-10	Patargo	patargo	46	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
335	DO-DJO-11	Pélébina	pelebina	46	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
336	DO-DJO-12	Sérou	serou	46	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
337	DO-OUA-01	Badjoudè	badjoude	47	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
338	DO-OUA-02	Kondé	konde	47	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
339	DO-OUA-03	Ouaké	ouake	47	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
340	DO-OUA-04	Sèmèrè I	semere-i	47	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
341	DO-OUA-05	Sèmèrè II	semere-ii	47	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
342	DO-OUA-06	Tchalinga	tchalinga	47	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
343	LI-COT-01	1er arrondissement de Cotonou	1er-arrondissement-de-cotonou	48	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
344	LI-COT-02	2e arrondissement de Cotonou	2e-arrondissement-de-cotonou	48	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
345	LI-COT-03	3e arrondissement de Cotonou	3e-arrondissement-de-cotonou	48	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
346	LI-COT-04	4e arrondissement de Cotonou	4e-arrondissement-de-cotonou	48	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
347	LI-COT-05	5e arrondissement de Cotonou	5e-arrondissement-de-cotonou	48	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
348	LI-COT-06	6e arrondissement de Cotonou	6e-arrondissement-de-cotonou	48	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
349	LI-COT-07	7e arrondissement de Cotonou	7e-arrondissement-de-cotonou	48	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
350	LI-COT-08	8e arrondissement de Cotonou	8e-arrondissement-de-cotonou	48	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
351	LI-COT-09	9e arrondissement de Cotonou	9e-arrondissement-de-cotonou	48	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
352	LI-COT-10	10e arrondissement de Cotonou	10e-arrondissement-de-cotonou	48	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
353	LI-COT-11	11e arrondissement de Cotonou	11e-arrondissement-de-cotonou	48	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
354	LI-COT-12	12e arrondissement de Cotonou	12e-arrondissement-de-cotonou	48	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
355	LI-COT-13	13e arrondissement de Cotonou	13e-arrondissement-de-cotonou	48	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
356	MO-ATH-01	Adohoun	adohoun	49	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
357	MO-ATH-02	Atchannou	atchannou	49	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
358	MO-ATH-03	Athiémé	athieme	49	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
359	MO-ATH-04	Dédékpoé	dedekpoe	49	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
360	MO-ATH-05	Kpinnou	kpinnou	49	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
361	MO-BOP-01	Agbodji	agbodji	50	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
362	MO-BOP-02	Badazoui	badazoui	50	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
363	MO-BOP-03	Bopa	bopa	50	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
364	MO-BOP-04	Gbakpodji	gbakpodji	50	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
365	MO-BOP-05	Lobogo	lobogo	50	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
366	MO-BOP-06	Possotomè	possotome	50	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
367	MO-BOP-07	Yégodoé	yegodoe	50	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
368	MO-COM-01	Agatogbo	agatogbo	51	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
369	MO-COM-02	Akodéha	akodeha	51	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
370	MO-COM-03	Comè	come	51	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
371	MO-COM-04	Ouèdèmè-Pédah	ouedeme-pedah	51	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
372	MO-COM-05	Oumako	oumako	51	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
373	MO-GPO-01	Adjaha	adjaha	52	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
374	MO-GPO-02	Agoué	agoue	52	2025-07-28 17:22:21	2025-07-28 17:22:21	\N
375	MO-GPO-03	Avloh	avloh	52	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
376	MO-GPO-04	Djanglanmey	djanglanmey	52	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
377	MO-GPO-05	Gbéhoué	gbehoue	52	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
378	MO-GPO-06	Grand-Popo	grand-popo	52	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
379	MO-GPO-07	Sazoué	sazoue	52	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
380	MO-HOU-01	Dahé	dahe	53	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
381	MO-HOU-02	Doutou	doutou	53	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
382	MO-HOU-03	Honhoué	honhoue	53	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
383	MO-HOU-04	Houéyogbé	houeyogbe	53	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
384	MO-HOU-05	Sè	se1	53	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
385	MO-HOU-06	Zoungbonou	zoungbonou	53	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
386	MO-LOK-01	Agamé	agame	54	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
387	MO-LOK-02	Houin	houin	54	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
388	MO-LOK-03	Koudo	koudo	54	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
389	MO-LOK-04	Lokossa et Ouèdèmè	lokossa-et-ouedeme	54	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
390	OU-ADJ-01	Adjarra I	adjarra-i	55	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
391	OU-ADJ-02	Adjarra II	adjarra-ii	55	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
392	OU-ADJ-03	Aglogbé	aglogbe	55	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
393	OU-ADJ-04	Honvié	honvie	55	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
394	OU-ADJ-05	Malanhoui	malanhoui	55	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
395	OU-ADJ-06	Médédjonou	mededjonou	55	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
396	OU-ADH-01	Adjohoun	adjohoun	56	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
397	OU-ADH-02	Akpadanou	akpadanou	56	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
398	OU-ADH-03	Awonou	awonou	56	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
399	OU-ADH-04	Azowlissè	azowlisse	56	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
400	OU-ADH-05	Dèmè	deme	56	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
401	OU-ADH-06	Gangban	gangban	56	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
402	OU-ADH-07	Kodè	kode	56	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
403	OU-ADH-08	Togbota	togbota	56	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
404	OU-AGU-01	Avagbodji	avagbodji	57	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
405	OU-AGU-02	Houédomè	houedome	57	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
406	OU-AGU-03	Zoungamè	zoungame	57	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
407	OU-AKM-01	Akpro-Missérété	akpro-misserete	58	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
408	OU-AKM-02	Gomè-Sota	gome-sota	58	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
409	OU-AKM-03	Katagon	katagon	58	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
410	OU-AKM-04	Vakon	vakon	58	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
411	OU-AKM-05	Zodogbomey	zodogbomey	58	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
412	OU-AVR-01	Atchoukpa	atchoukpa	59	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
413	OU-AVR-02	Avrankou	avrankou	59	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
414	OU-AVR-03	Djomon	djomon	59	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
415	OU-AVR-04	Gbozounmè	gbozounme	59	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
416	OU-AVR-05	Kouty	kouty	59	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
417	OU-AVR-06	Ouanho	ouanho	59	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
418	OU-AVR-07	Sado	sado	59	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
419	OU-BON-01	Affamè	affame	60	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
420	OU-BON-02	Atchonsa	atchonsa	60	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
421	OU-BON-03	Bonou	bonou	60	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
422	OU-BON-04	Damè-Wogon	dame-wogon	60	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
423	OU-BON-05	Houinviguè	houinvigue	60	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
424	OU-DAN-01	Dangbo	dangbo	61	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
425	OU-DAN-02	Dèkin	dekin	61	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
426	OU-DAN-03	Gbéko	gbeko	61	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
427	OU-DAN-04	Houédomey	houedomey	61	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
428	OU-DAN-05	Hozin	hozin	61	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
429	OU-DAN-06	Késsounou	kessounou	61	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
430	OU-DAN-07	Zounguè	zoungue	61	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
431	OU-PNV-01	1er arrondissement	1er-arrondissement	62	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
432	OU-PNV-02	2e arrondissement	2e-arrondissement	62	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
433	OU-PNV-03	3e arrondissement	3e-arrondissement	62	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
434	OU-PNV-04	4e arrondissement	4e-arrondissement	62	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
435	OU-PNV-05	5e arrondissement	5e-arrondissement	62	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
436	OU-SKP-01	Agblangandan	agblangandan	63	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
437	OU-SKP-02	Aholouyèmè	aholouyeme	63	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
438	OU-SKP-03	Djèrègbè	djeregbe	63	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
439	OU-SKP-04	Ekpè	ekpe	63	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
440	OU-SKP-05	Sèmè-Kpodji	seme-kpodji	63	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
441	OU-SKP-06	Tohouè	tohoue	63	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
442	PL-AOU-01	Adja-Ouèrè	adja-ouere	64	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
443	PL-AOU-02	Ikpinlè	ikpinle	64	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
444	PL-AOU-03	Kpoulou	kpoulou	64	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
445	PL-AOU-04	Massè	masse	64	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
446	PL-AOU-05	Oko-Akarè	oko-akare	64	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
447	PL-AOU-06	Totonnoukon	totonnoukon	64	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
448	PL-IFA-01	Banigbé	banigbe1	65	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
449	PL-IFA-02	Daagbé	daagbe	65	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
450	PL-IFA-03	Ifangni	ifangni	65	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
451	PL-IFA-04	Ko-Koumolou	ko-koumolou	65	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
452	PL-IFA-05	Lagbé	lagbe	65	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
453	PL-IFA-06	Tchaada	tchaada	65	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
454	PL-KET-01	Adakplamé	adakplame	66	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
455	PL-KET-02	Idigny	idigny	66	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
456	PL-KET-03	Kpankou	kpankou	66	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
457	PL-KET-04	Kétou	ketou	66	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
458	PL-KET-05	Odometa	odometa	66	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
459	PL-KET-06	Okpometa	okpometa	66	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
460	PL-POB-01	Ahoyéyé	ahoyeye	67	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
461	PL-POB-02	Igana	igana	67	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
462	PL-POB-03	Issaba	issaba	67	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
463	PL-POB-04	Pobè	pobe	67	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
464	PL-POB-05	Towé	towe	67	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
465	PL-SAK-01	Aguidi	aguidi	68	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
466	PL-SAK-02	Ita-Djèbou	ita-djebou	68	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
467	PL-SAK-03	Sakété I	sakete-i	68	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
468	PL-SAK-04	Sakété II	sakete-ii	68	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
469	PL-SAK-05	Takon	takon	68	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
470	PL-SAK-06	Yoko	yoko	68	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
471	ZO-ABO-01	Agbokpa	agbokpa	69	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
472	ZO-ABO-02	Dètohou	detohou	69	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
473	ZO-ABO-03	Djègbè	djegbe1	69	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
474	ZO-ABO-04	Hounli	hounli	69	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
475	ZO-ABO-05	Sèhoun	sehoun	69	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
476	ZO-ABO-06	Vidolè	vidole	69	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
477	ZO-ABO-07	Zounzounmè	zounzounme	69	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
478	ZO-AGB-01	Adahondjigon	adahondjigon	70	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
479	ZO-AGB-02	Adingningon	adingningon	70	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
480	ZO-AGB-03	Agbangnizoun	agbangnizoun	70	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
481	ZO-AGB-04	Kinta	kinta	70	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
482	ZO-AGB-05	Kpota	kpota	70	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
483	ZO-AGB-06	Lissazounmè	lissazounme	70	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
484	ZO-AGB-07	Sahé	sahe	70	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
485	ZO-AGB-08	Siwé	siwe	70	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
486	ZO-AGB-09	Tanvé	tanve	70	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
487	ZO-AGB-10	Zoungoudo	zoungoudo	70	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
488	ZO-BOH-01	Agongointo	agongointo	71	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
489	ZO-BOH-02	Avogbanna	avogbanna	71	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
490	ZO-BOH-03	Bohicon I	bohicon-i	71	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
491	ZO-BOH-04	Bohicon II	bohicon-ii	71	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
492	ZO-BOH-05	Gnidjazoun	gnidjazoun	71	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
493	ZO-BOH-06	Lissèzoun	lissezoun	71	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
494	ZO-BOH-07	Ouassaho	ouassaho	71	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
495	ZO-BOH-08	Passagon	passagon	71	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
496	ZO-BOH-09	Saclo	saclo	71	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
497	ZO-BOH-10	Sodohomè	sodohome	71	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
498	ZO-COV-01	Adogbé	adogbe	72	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
499	ZO-COV-02	Gounli	gounli	72	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
500	ZO-COV-03	Houéko	houeko	72	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
501	ZO-COV-04	Houen-Hounso	houen-hounso	72	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
502	ZO-COV-05	Lainta-Cogbè	lainta-cogbe	72	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
503	ZO-COV-06	Naogon	naogon	72	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
504	ZO-COV-07	Soli	soli	72	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
505	ZO-COV-08	Zogba	zogba	72	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
506	ZO-DJI-01	Agondji	agondji	73	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
507	ZO-DJI-02	Agouna	agouna	73	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
508	ZO-DJI-03	Dan	dan	73	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
509	ZO-DJI-04	Djidja	djidja	73	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
510	ZO-DJI-05	Dohouimè	dohouime	73	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
511	ZO-DJI-06	Gobaix	gobaix	73	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
512	ZO-DJI-07	Monsourou	monsourou	73	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
513	ZO-DJI-08	Mougnon	mougnon	73	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
514	ZO-DJI-09	Oungbègamè	oungbegame	73	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
515	ZO-DJI-10	Houto	houto	73	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
516	ZO-DJI-11	Setto	setto	73	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
517	ZO-DJI-12	Zoukon	zoukon	73	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
518	ZO-OUIN-01	Dasso	dasso	74	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
519	ZO-OUIN-02	Ouinhi	ouinhi	74	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
520	ZO-OUIN-03	Sagon	sagon	74	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
521	ZO-OUIN-04	Tohoué	tohoue1	74	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
522	ZO-ZKP-01	Allahé	allahe	75	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
523	ZO-ZKP-02	Assalin	assalin	75	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
524	ZO-ZKP-03	Houngomey	houngomey	75	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
525	ZO-ZKP-04	Kpakpamè	kpakpame	75	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
526	ZO-ZKP-05	Kpozoun	kpozoun	75	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
527	ZO-ZKP-06	Za-Kpota	za-kpota	75	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
528	ZO-ZKP-07	Za-Tanta	za-tanta	75	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
529	ZO-ZKP-08	Zèko	zeko	75	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
530	ZO-ZAG-01	Agonli-Houégbo	agonli-houegbo	76	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
531	ZO-ZAG-02	Banamè	baname	76	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
532	ZO-ZAG-03	N'-Tan	n-tan	76	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
533	ZO-ZAG-04	Dovi	dovi	76	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
534	ZO-ZAG-05	Kpédékpo	kpedekpo	76	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
535	ZO-ZAG-06	Zagnanado	zagnanado	76	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
536	ZO-ZOG-01	Akiza	akiza	77	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
537	ZO-ZOG-02	Avlamè	avlame	77	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
538	ZO-ZOG-03	Cana I	cana-i	77	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
539	ZO-ZOG-04	Cana II	cana-ii	77	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
540	ZO-ZOG-05	Domè	dome	77	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
541	ZO-ZOG-06	Koussoukpa	koussoukpa	77	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
542	ZO-ZOG-07	Kpokissa	kpokissa	77	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
543	ZO-ZOG-08	Massi	massi	77	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
544	ZO-ZOG-09	Tanwé-Hessou	tanwe-hessou	77	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
545	ZO-ZOG-10	Zogbodomey	zogbodomey	77	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
546	ZO-ZOG-11	Zoukou	zoukou	77	2025-07-28 17:22:22	2025-07-28 17:22:22	\N
\.


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache (key, value, expiration) FROM stdin;
bip-cache-5c785c036466adea360111aa28563bfd556b5fba:timer	i:1753384702;	1753384702
bip-cache-5c785c036466adea360111aa28563bfd556b5fba	i:1;	1753384702
bip-cache-mbouraima@gmail.com|127.0.0.1:timer	i:1753385112;	1753385112
bip-cache-mbouraima@gmail.com|127.0.0.1	i:1;	1753385112
bip-cache-alaomoutawakil@gmail.com|192.168.8.103:timer	i:1753457407;	1753457407
bip-cache-alaomoutawakil@gmail.com|192.168.8.103	i:1;	1753457407
bip-cache-moutawakilalao@gmail.com|192.168.8.127:timer	i:1753695538;	1753695538
bip-cache-moutawakilalao@gmail.com|192.168.8.127	i:1;	1753695538
bip-cache-moutawakilbouraima@celeriteholding.com|192.168.8.127:timer	i:1753729514;	1753729514
bip-cache-moutawakilbouraima@celeriteholding.com|192.168.8.127	i:2;	1753729514
bip-cache-alaomoutawakil@gmail.com|127.0.0.1:timer	i:1753384705;	1753384705
bip-cache-alaomoutawakil@gmail.com|127.0.0.1	i:2;	1753384705
bip-cache-mukendi.jeanpierre@plan.gov.cd|192.168.8.103:timer	i:1753373979;	1753373979
bip-cache-mukendi.jeanpierre@plan.gov.cd|192.168.8.103	i:1;	1753373979
bip-cache-356a192b7913b04c54574d18c28d46e6395428ab:timer	i:1753469581;	1753469581
bip-cache-356a192b7913b04c54574d18c28d46e6395428ab	i:2;	1753469581
bip-cache-mbouraima@gmail.com|192.168.8.127:timer	i:1753709183;	1753709183
bip-cache-mbouraima@gmail.com|192.168.8.127	i:1;	1753709183
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: categories_critere; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.categories_critere (id, type, slug, is_mandatory, criteres_ajustable, created_at, updated_at, deleted_at) FROM stdin;
1	Évaluation préliminaire multi projet de l'impact climatique	evaluation-preliminaire-multi-projet-impact-climatique	t	\N	2025-07-29 09:01:59	2025-07-29 09:01:59	\N
7	Évaluation multi-critere	Évaluation-multi-critere	t	\N	2025-07-29 11:59:57	2025-07-29 11:59:57	\N
\.


--
-- Data for Name: categories_document; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.categories_document (id, nom, slug, description, format, created_at, updated_at, deleted_at) FROM stdin;
2	Formulaire standard d\\'ideation de projet	formulaire-standard-d'ideation-de-projet	Formulaire standard d'ideation de projet	document	2025-07-18 12:57:42	2025-07-18 12:57:42	\N
4	Formulaire standard ideation de projet	formulaire-standard-ideation-de-projet	Formulaire standard d'ideation de projet	document	2025-07-18 12:58:31	2025-07-18 12:58:31	\N
6	Fiche d\\'idée	fiche-idee	Formulaire standard d'ideation de projet	document	2025-07-22 14:27:55	2025-07-22 14:27:55	\N
\.


--
-- Data for Name: categories_projet; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.categories_projet (id, categorie, slug, created_at, updated_at, deleted_at) FROM stdin;
1	Categorie de projet	categorie-idee	2025-07-23 07:54:37	2025-07-23 07:54:37	\N
\.


--
-- Data for Name: champs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.champs (id, label, info, attribut, placeholder, is_required, default_value, "isEvaluated", ordre_affichage, type_champ, "sectionId", "documentId", meta_options, created_at, updated_at, deleted_at, champ_standard) FROM stdin;
176	Arrondissements		arrondissements	Choisissez un arrondissement	f	\N	f	6	select	39	40	{"configs": {"options": []}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
189	Parties prenantes		parties_prenantes	Identifiez les parties prenantes impliquées	t	\N	f	6	textarea	41	40	{"configs": {"max_length": 1000, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
213	Constats majeurs		constats_majeurs		t	\N	f	5	textarea	41	40	{"configs": {"max_length": 1000, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}	2025-07-23 05:57:41	2025-07-25 10:47:43	\N	t
187	Sources de financement		sources_financement	Choisissez une source	f	\N	f	3	select	41	40	{"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
188	Public cible		public_cible	Décrivez le public cible du projet	t	\N	f	4	textarea	41	40	{"configs": {"max_length": 1000, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
204	Estimation des coûts et benefices		estimation_couts		f	\N	f	4	textarea	43	40	{"configs": {"max_length": 1500, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
177	Villages		villages	Selectionnez les villages	f	\N	f	7	select	39	40	{"configs": {"multiple": true, "max_length": 255, "min_length": 1}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
182	Résultats stratégique		resultats_strategiques	Choisissez un résultat	f	\N	f	5	select	40	40	{"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
183	Axes du pag		axes_pag	Choisissez les axes du pags	f	\N	f	8	select	40	40	{"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
184	Actions du pag		actions_pag	Choisissez une action	f	\N	f	9	select	40	40	{"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
178	Odds		odds	Sélectionnez un ODD	t	\N	f	1	select	40	40	{"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
212	Piliers du pag		piliers_pag	Choisissez les piliers	f	\N	f	7	select	40	40	{"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}	2025-07-23 05:57:41	2025-07-25 10:47:43	\N	t
180	Orientations stratégique		orientations_strategiques	Choisissez une orientation	t	\N	f	3	select	40	40	{"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
181	Objectifs stratégique		objectifs_strategiques	Choisissez un objectif	f	\N	f	4	select	40	40	{"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
185	Types de financement		types_financement	Choisissez un type	t	\N	f	1	select	41	40	{"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
186	Natures du financement		natures_financement	Choisissez une nature	f	\N	f	2	select	41	40	{"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
161	Titre du projet		titre_projet	Saisissez le titre de votre projet	t	\N	f	1	text	38	40	{"configs": {"max_length": 255, "min_length": 1}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
162	Sigle du projet		sigle	Acronyme du projet	f	\N	f	2	text	38	40	{"configs": {"max_length": 50, "min_length": 1}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
163	Categorie de projet		categorieId	Nom du ministère de rattachement	t	\N	f	3	select	38	40	{"configs": {"max_length": 255, "min_length": 1}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
165	Durée		duree	Ex: 24 mois	t	\N	f	4	number	38	40	{"configs": {"max_length": 100, "min_length": 1}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
169	Coût en euro		cout_euro	0	t	0	f	5	number	38	40	{"configs": {"max": null, "min": 0, "step": 1}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
170	Coût en dollar canadien		cout_dollar_canadien	0	t	0	f	5	number	38	40	{"configs": {"max": null, "min": 0, "step": 1}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
166	Coût estimatif du projet		cout_estimatif_projet	0	t	0	f	5	number	38	40	{"configs": {"max": null, "min": 0, "step": 1}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
168	Coût en dollar americain		cout_dollar_americain	0	t	0	f	5	number	38	40	{"configs": {"max": null, "min": 0, "step": 1}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
171	Grand Secteur		grand_secteur	Choisissez un grand secteur	t	\N	f	1	select	39	40	{"configs": {"options": []}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
172	Secteur		secteur	Choisissez un secteur	t	\N	f	2	select	39	40	{"configs": {"options": []}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
173	Sous Secteur		secteurId	Choisissez un sous secteur	f	\N	f	3	select	39	40	{"configs": {"options": []}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
174	Départements		departements	Choisissez un département	t	\N	f	4	select	39	40	{"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
175	Communes		communes	Choisissez une commune	f	\N	f	5	select	39	40	{"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
179	Cibles		cibles	Sélectionnez les cibles	f	\N	f	2	select	40	40	{"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
196	Situation désirée		situation_desiree	Décrivez la situation visée	t	\N	f	6	textarea	42	40	{"configs": {"max_length": 2000, "min_length": 20}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
197	Contraintes		contraintes	Identifiez les principales contraintes	f	\N	f	7	textarea	42	40	{"configs": {"max_length": 1000, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
190	Objectif du projet		objectif_general	Décrivez l'objectif principal du projet	t	\N	f	1	textarea	42	40	{"configs": {"max_length": 2000, "min_length": 20}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
191	Objectif Specifiques		objectifs_specifiques	Décrivez l'objectif principal du projet	t	\N	f	1	textarea	42	40	{"configs": {"max_length": 2000, "min_length": 20}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
192	Résultats attendus		resultats_attendus	Décrivez les résultats attendus	t	\N	f	2	textarea	42	40	{"configs": {"max_length": 2000, "min_length": 20}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
193	Origine du projet		origine	D'où vient l'idée de ce projet ?	t	\N	f	3	textarea	42	40	{"configs": {"max_length": 1500, "min_length": 20}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
194	Fondement du projet		fondement	Sur quoi se base ce projet ?	t	\N	f	4	textarea	42	40	{"configs": {"max_length": 1500, "min_length": 20}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
195	Situation actuelle		situation_actuelle	Décrivez la situation actuelle	t	\N	f	5	textarea	42	40	{"configs": {"max_length": 2000, "min_length": 20}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
201	Caractéristiques techniques		caracteristiques_techniques	Caractéristiques techniques	f	\N	f	2	textarea	43	40	{"configs": {"max_length": 2000, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
207	Autre solutions alternatives considere et non retenues		description	Autre solutions alternatives	f	\N	f	6	textarea	43	40	{"configs": {"max_length": 1500, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
208	Description sommaire		sommaire	Description sommaire	f	\N	f	6	textarea	43	40	{"configs": {"max_length": 1500, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
202	Impact environnemental		impact_environnement	Impact sur l'environnement	f	\N	f	3	textarea	43	40	{"configs": {"max_length": 1500, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
203	Aspects organisationnels		aspect_organisationnel		f	\N	f	4	textarea	43	40	{"configs": {"max_length": 1500, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
198	Description du projet		description_projet	Description détaillée du projet	t	\N	f	1	textarea	43	40	{"configs": {"max_length": 3000, "min_length": 50}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
200	Échéancier du projet		echeancier	Description détaillée du projet	t	\N	f	1	textarea	43	40	{"configs": {"max_length": 3000, "min_length": 50}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
199	Description du projet		description_extrants	Description détaillée du projet	t	\N	f	1	textarea	43	40	{"configs": {"max_length": 3000, "min_length": 50}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
205	Risques immédiats		risques_immediats	Risques identifiés	f	\N	f	5	textarea	43	40	{"configs": {"max_length": 1500, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
206	Conclusions		conclusions	Conclusions générales	f	\N	f	6	textarea	43	40	{"configs": {"max_length": 1500, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}	2025-07-23 05:18:58	2025-07-25 10:47:43	\N	t
\.


--
-- Data for Name: champs_projet; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.champs_projet (id, valeur, commentaire, projetable_type, projetable_id, "champId", created_at, updated_at, deleted_at) FROM stdin;
1	"onsequaturd fhg rerum"	\N	App\\Models\\IdeeProjet	7	161	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
2	"Sunt gfhg ddsfsfddfh"	\N	App\\Models\\IdeeProjet	7	162	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
3	1	\N	App\\Models\\IdeeProjet	7	163	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
4	[6]	\N	App\\Models\\IdeeProjet	7	165	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
5	39	\N	App\\Models\\IdeeProjet	7	169	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
6	38	\N	App\\Models\\IdeeProjet	7	170	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
7	{"devise": "FCFA", "montant": 77}	\N	App\\Models\\IdeeProjet	7	166	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
8	58	\N	App\\Models\\IdeeProjet	7	168	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
9	[1]	\N	App\\Models\\IdeeProjet	7	176	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
10	[6]	\N	App\\Models\\IdeeProjet	7	177	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
11	19	\N	App\\Models\\IdeeProjet	7	171	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
12	20	\N	App\\Models\\IdeeProjet	7	172	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
13	28	\N	App\\Models\\IdeeProjet	7	173	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
14	[3]	\N	App\\Models\\IdeeProjet	7	174	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
15	[1]	\N	App\\Models\\IdeeProjet	7	175	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
16	[8]	\N	App\\Models\\IdeeProjet	7	180	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
17	[14]	\N	App\\Models\\IdeeProjet	7	181	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
18	[4]	\N	App\\Models\\IdeeProjet	7	182	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
19	[3]	\N	App\\Models\\IdeeProjet	7	183	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
20	[10]	\N	App\\Models\\IdeeProjet	7	184	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
21	[9]	\N	App\\Models\\IdeeProjet	7	178	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
22	[7]	\N	App\\Models\\IdeeProjet	7	212	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
23	[4]	\N	App\\Models\\IdeeProjet	7	179	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
24	[10]	\N	App\\Models\\IdeeProjet	7	187	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
25	"jhgggghh"	\N	App\\Models\\IdeeProjet	7	188	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
26	["jhghj"]	\N	App\\Models\\IdeeProjet	7	189	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
27	"ghj"	\N	App\\Models\\IdeeProjet	7	213	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
28	4	\N	App\\Models\\IdeeProjet	7	185	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
29	6	\N	App\\Models\\IdeeProjet	7	186	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
30	["Velit quis voluptate dolor assumenda"]	\N	App\\Models\\IdeeProjet	7	192	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
31	"Ad sed est et voluptatibus natus possimus dolor sed sit eius dolores velit possimus velit dolore voluptate ut"	\N	App\\Models\\IdeeProjet	7	193	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
32	"Consequat Et temporibus exercitationem molestiae aliqua Qui"	\N	App\\Models\\IdeeProjet	7	194	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
33	"Maxime rerum est aliqua Nesciunt sit possimus minima qui et ullamco"	\N	App\\Models\\IdeeProjet	7	195	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
34	"Veniam iure molestias ab amet et tempor aut similique"	\N	App\\Models\\IdeeProjet	7	196	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
35	"Pariatur Fuga Omnis voluptas ipsum vel quos expedita harum maxime aut est"	\N	App\\Models\\IdeeProjet	7	197	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
36	"Fugiat sint est vero deleniti sint voluptatibus officiis et mollit ut culpa temporibus expedita eu ad anim non"	\N	App\\Models\\IdeeProjet	7	190	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
37	["Quis pariatur Voluptatem omnis ad qui"]	\N	App\\Models\\IdeeProjet	7	191	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
38	"r earum nihil qui dolores dolores provident velit"	\N	App\\Models\\IdeeProjet	7	204	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
39	"t labore in"	\N	App\\Models\\IdeeProjet	7	201	2025-07-24 20:02:01	2025-07-24 20:02:01	\N
40	"ae quis omnis consequatur aut magnam porro"	\N	App\\Models\\IdeeProjet	7	200	2025-07-24 20:02:01	2025-07-24 20:02:01	\N
41	"tatis ea nihil occaecat obcaecati"	\N	App\\Models\\IdeeProjet	7	205	2025-07-24 20:02:01	2025-07-24 20:02:01	\N
42	"periores nihil est aut qui ratione ad in"	\N	App\\Models\\IdeeProjet	7	206	2025-07-24 20:02:01	2025-07-24 20:02:01	\N
43	"epudiandae consequat Atque in id blanditiis tempor"	\N	App\\Models\\IdeeProjet	7	207	2025-07-24 20:02:01	2025-07-24 20:02:01	\N
44	"r quis officia libero"	\N	App\\Models\\IdeeProjet	7	208	2025-07-24 20:02:01	2025-07-24 20:02:01	\N
45	"Sit quisquam officiis omnis eos in quis quis"	\N	App\\Models\\IdeeProjet	7	202	2025-07-24 20:02:01	2025-07-24 20:02:01	\N
46	"Quo consequatur nobis non blanditiis deleniti ut"	\N	App\\Models\\IdeeProjet	7	203	2025-07-24 20:02:01	2025-07-24 20:02:01	\N
47	"Consequat Sint rerum cumque nulla vel cillum"	\N	App\\Models\\IdeeProjet	7	198	2025-07-24 20:02:01	2025-07-24 20:02:01	\N
48	"que fugiat consectetur aliquid qui et labore"	\N	App\\Models\\IdeeProjet	7	199	2025-07-24 20:02:01	2025-07-24 20:02:01	\N
49	"onsequaturdre fhg rerum"	\N	App\\Models\\IdeeProjet	8	161	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
50	"Sunt gfhgdsfds ddsfsfddfh"	\N	App\\Models\\IdeeProjet	8	162	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
51	1	\N	App\\Models\\IdeeProjet	8	163	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
52	[6]	\N	App\\Models\\IdeeProjet	8	165	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
53	39	\N	App\\Models\\IdeeProjet	8	169	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
54	38	\N	App\\Models\\IdeeProjet	8	170	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
55	{"devise": "FCFA", "montant": 77}	\N	App\\Models\\IdeeProjet	8	166	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
56	58	\N	App\\Models\\IdeeProjet	8	168	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
57	[1]	\N	App\\Models\\IdeeProjet	8	176	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
58	[6]	\N	App\\Models\\IdeeProjet	8	177	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
59	19	\N	App\\Models\\IdeeProjet	8	171	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
60	20	\N	App\\Models\\IdeeProjet	8	172	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
61	28	\N	App\\Models\\IdeeProjet	8	173	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
62	[3]	\N	App\\Models\\IdeeProjet	8	174	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
63	[1]	\N	App\\Models\\IdeeProjet	8	175	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
64	[8]	\N	App\\Models\\IdeeProjet	8	180	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
65	[14]	\N	App\\Models\\IdeeProjet	8	181	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
66	[4]	\N	App\\Models\\IdeeProjet	8	182	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
67	[3]	\N	App\\Models\\IdeeProjet	8	183	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
68	[10]	\N	App\\Models\\IdeeProjet	8	184	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
69	[9]	\N	App\\Models\\IdeeProjet	8	178	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
70	[7]	\N	App\\Models\\IdeeProjet	8	212	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
71	[4]	\N	App\\Models\\IdeeProjet	8	179	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
72	[10]	\N	App\\Models\\IdeeProjet	8	187	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
73	"jhgggghh"	\N	App\\Models\\IdeeProjet	8	188	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
74	["jhghj"]	\N	App\\Models\\IdeeProjet	8	189	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
75	"ghj"	\N	App\\Models\\IdeeProjet	8	213	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
76	4	\N	App\\Models\\IdeeProjet	8	185	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
77	6	\N	App\\Models\\IdeeProjet	8	186	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
78	["Velit quis voluptate dolor assumenda"]	\N	App\\Models\\IdeeProjet	8	192	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
79	"Ad sed est et voluptatibus natus possimus dolor sed sit eius dolores velit possimus velit dolore voluptate ut"	\N	App\\Models\\IdeeProjet	8	193	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
80	"Consequat Et temporibus exercitationem molestiae aliqua Qui"	\N	App\\Models\\IdeeProjet	8	194	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
81	"Maxime rerum est aliqua Nesciunt sit possimus minima qui et ullamco"	\N	App\\Models\\IdeeProjet	8	195	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
82	"Veniam iure molestias ab amet et tempor aut similique"	\N	App\\Models\\IdeeProjet	8	196	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
83	"Pariatur Fuga Omnis voluptas ipsum vel quos expedita harum maxime aut est"	\N	App\\Models\\IdeeProjet	8	197	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
84	"Fugiat sint est vero deleniti sint voluptatibus officiis et mollit ut culpa temporibus expedita eu ad anim non"	\N	App\\Models\\IdeeProjet	8	190	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
85	["Quis pariatur Voluptatem omnis ad qui"]	\N	App\\Models\\IdeeProjet	8	191	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
86	"r earum nihil qui dolores dolores provident velit"	\N	App\\Models\\IdeeProjet	8	204	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
87	"t labore in"	\N	App\\Models\\IdeeProjet	8	201	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
88	"ae quis omnis consequatur aut magnam porro"	\N	App\\Models\\IdeeProjet	8	200	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
89	"tatis ea nihil occaecat obcaecati"	\N	App\\Models\\IdeeProjet	8	205	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
90	"periores nihil est aut qui ratione ad in"	\N	App\\Models\\IdeeProjet	8	206	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
91	"epudiandae consequat Atque in id blanditiis tempor"	\N	App\\Models\\IdeeProjet	8	207	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
92	"r quis officia libero"	\N	App\\Models\\IdeeProjet	8	208	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
93	"Sit quisquam officiis omnis eos in quis quis"	\N	App\\Models\\IdeeProjet	8	202	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
94	"Quo consequatur nobis non blanditiis deleniti ut"	\N	App\\Models\\IdeeProjet	8	203	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
95	"Consequat Sint rerum cumque nulla vel cillum"	\N	App\\Models\\IdeeProjet	8	198	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
96	"que fugiat consectetur aliquid qui et labore"	\N	App\\Models\\IdeeProjet	8	199	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
97	"que cumque mollit nostrud ut et"	\N	App\\Models\\IdeeProjet	13	161	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
98	"niti dolor similique enim"	\N	App\\Models\\IdeeProjet	13	162	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
99	1	\N	App\\Models\\IdeeProjet	13	163	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
100	[23]	\N	App\\Models\\IdeeProjet	13	165	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
101	9	\N	App\\Models\\IdeeProjet	13	169	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
102	66	\N	App\\Models\\IdeeProjet	13	170	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
103	{"devise": "FCFA", "montant": 5}	\N	App\\Models\\IdeeProjet	13	166	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
104	52	\N	App\\Models\\IdeeProjet	13	168	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
105	[1]	\N	App\\Models\\IdeeProjet	13	176	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
106	[6]	\N	App\\Models\\IdeeProjet	13	177	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
107	19	\N	App\\Models\\IdeeProjet	13	171	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
108	20	\N	App\\Models\\IdeeProjet	13	172	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
109	27	\N	App\\Models\\IdeeProjet	13	173	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
110	[3]	\N	App\\Models\\IdeeProjet	13	174	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
111	[1]	\N	App\\Models\\IdeeProjet	13	175	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
112	[8]	\N	App\\Models\\IdeeProjet	13	180	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
113	[14]	\N	App\\Models\\IdeeProjet	13	181	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
114	[4]	\N	App\\Models\\IdeeProjet	13	182	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
115	[3]	\N	App\\Models\\IdeeProjet	13	183	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
116	[10]	\N	App\\Models\\IdeeProjet	13	184	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
117	[10]	\N	App\\Models\\IdeeProjet	13	178	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
118	[7]	\N	App\\Models\\IdeeProjet	13	212	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
119	[5]	\N	App\\Models\\IdeeProjet	13	179	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
120	[10]	\N	App\\Models\\IdeeProjet	13	187	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
121	"Qui sint nihil enim ut"	\N	App\\Models\\IdeeProjet	13	188	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
122	["Nisi non magnam doloribus sit corporis ratione suscipit consequuntur eu ea nobis est culpa doloremque id dicta quae vero incidunt"]	\N	App\\Models\\IdeeProjet	13	189	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
123	"Id nihil quod soluta expedita sit tempore iusto magnam sapiente enim quia aperiam qui exercitation ad qui corporis"	\N	App\\Models\\IdeeProjet	13	213	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
124	4	\N	App\\Models\\IdeeProjet	13	185	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
125	6	\N	App\\Models\\IdeeProjet	13	186	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
126	["Nisi qui sint nesciunt magni dolorem autem vitae eius molestiae nulla sunt fuga Ipsam"]	\N	App\\Models\\IdeeProjet	13	192	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
127	"Nulla veniam est proident dolorem recusandae Eveniet earum facilis sunt consequat Sit dicta eum maxime consequatur amet"	\N	App\\Models\\IdeeProjet	13	193	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
128	"Incidunt sunt earum tempor eu qui at consequuntur eos laboriosam esse officia quaerat voluptate nostrum eos fugiat ea reiciendis minima"	\N	App\\Models\\IdeeProjet	13	194	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
129	"Consequuntur nostrud molestias suscipit nobis aut dolorem dolorum illo ut ipsum in"	\N	App\\Models\\IdeeProjet	13	195	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
130	"Voluptatum nostrud sed et enim harum eaque qui molestiae excepturi"	\N	App\\Models\\IdeeProjet	13	196	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
131	"Pariatur At sed voluptatem eos natus tempora est nihil aut"	\N	App\\Models\\IdeeProjet	13	197	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
132	"Mollit quia totam illum laboris labore necessitatibus ea iusto sed"	\N	App\\Models\\IdeeProjet	13	190	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
133	["Voluptatem quisquam minima qui quam incidunt voluptatem sed"]	\N	App\\Models\\IdeeProjet	13	191	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
134	"Sed rerum et sed sequi autem fugiat nulla quod eum molestiae eveniet duis accusantium corrupti est delectus quos"	\N	App\\Models\\IdeeProjet	13	204	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
135	"Voluptas modi et dolore exercitationem deserunt harum sunt et veritatis error consequat Velit"	\N	App\\Models\\IdeeProjet	13	201	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
136	"Ea provident quis vero cupidatat"	\N	App\\Models\\IdeeProjet	13	200	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
137	"Non voluptatibus a sed Nam ut porro eiusmod ullam repellendus Commodi inventore"	\N	App\\Models\\IdeeProjet	13	205	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
138	"Animi aspernatur ea dolor aute molestias"	\N	App\\Models\\IdeeProjet	13	206	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
139	"Odio alias est commodo nulla cillum consectetur qui quo et proident adipisci nihil et aliqua"	\N	App\\Models\\IdeeProjet	13	207	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
140	"Obcaecati fugiat quas quia est beatae voluptates exercitation enim adipisicing alias labore"	\N	App\\Models\\IdeeProjet	13	208	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
141	"Pariatur Aute nulla atque sed voluptatum do officia voluptatibus ad et nesciunt totam illum ratione"	\N	App\\Models\\IdeeProjet	13	202	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
142	"Optio quia voluptates velit aliquam facilis nemo in soluta eos totam optio sint inventore"	\N	App\\Models\\IdeeProjet	13	203	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
143	"Sed quia quod expedita nostrum error repudiandae explicabo Facilis distinctio Pariatur Eu"	\N	App\\Models\\IdeeProjet	13	198	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
144	"Quis iste provident vitae nostrum quaerat dolore eiusmod porro eius"	\N	App\\Models\\IdeeProjet	13	199	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
145	"hic velit velit placeat aute molestias nisi in in eum nihil amet"	\N	App\\Models\\IdeeProjet	15	161	2025-07-24 20:47:49	2025-07-24 20:47:49	\N
146	" elit incidunt s"	\N	App\\Models\\IdeeProjet	15	162	2025-07-24 20:47:49	2025-07-24 20:47:49	\N
147	1	\N	App\\Models\\IdeeProjet	15	163	2025-07-24 20:47:49	2025-07-24 20:47:49	\N
148	[93]	\N	App\\Models\\IdeeProjet	15	165	2025-07-24 20:47:49	2025-07-24 20:47:49	\N
149	35	\N	App\\Models\\IdeeProjet	15	169	2025-07-24 20:47:49	2025-07-24 20:47:49	\N
150	37	\N	App\\Models\\IdeeProjet	15	170	2025-07-24 20:47:49	2025-07-24 20:47:49	\N
151	{"devise": "FCFA", "montant": 4}	\N	App\\Models\\IdeeProjet	15	166	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
152	70	\N	App\\Models\\IdeeProjet	15	168	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
153	[1]	\N	App\\Models\\IdeeProjet	15	176	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
154	[6]	\N	App\\Models\\IdeeProjet	15	177	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
155	19	\N	App\\Models\\IdeeProjet	15	171	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
156	20	\N	App\\Models\\IdeeProjet	15	172	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
157	27	\N	App\\Models\\IdeeProjet	15	173	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
158	[3]	\N	App\\Models\\IdeeProjet	15	174	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
159	[1]	\N	App\\Models\\IdeeProjet	15	175	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
160	[8]	\N	App\\Models\\IdeeProjet	15	180	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
161	[14]	\N	App\\Models\\IdeeProjet	15	181	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
162	[4]	\N	App\\Models\\IdeeProjet	15	182	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
163	[3]	\N	App\\Models\\IdeeProjet	15	183	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
164	[10]	\N	App\\Models\\IdeeProjet	15	184	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
165	[12]	\N	App\\Models\\IdeeProjet	15	178	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
166	[7]	\N	App\\Models\\IdeeProjet	15	212	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
167	[4]	\N	App\\Models\\IdeeProjet	15	179	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
168	[10]	\N	App\\Models\\IdeeProjet	15	187	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
169	"iuhyg"	\N	App\\Models\\IdeeProjet	15	188	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
170	["uytr"]	\N	App\\Models\\IdeeProjet	15	189	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
171	"uytgf"	\N	App\\Models\\IdeeProjet	15	213	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
172	4	\N	App\\Models\\IdeeProjet	15	185	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
173	5	\N	App\\Models\\IdeeProjet	15	186	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
174	["Iste sit necessitatibus aperiam quisquam ut voluptas quae ex enim voluptas distinctio Sit et"]	\N	App\\Models\\IdeeProjet	15	192	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
175	"Sunt assumenda dolor voluptas nesciunt eligendi non et officiis"	\N	App\\Models\\IdeeProjet	15	193	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
235	"dfsd"	\N	App\\Models\\IdeeProjet	16	207	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
176	"Nesciunt reiciendis hic deleniti consequatur ducimus nostrum aspernatur exercitationem vero deleniti est quia"	\N	App\\Models\\IdeeProjet	15	194	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
177	"Minima animi dicta error ad rerum sint aut quidem inventore labore perspiciatis dolor et excepteur animi unde"	\N	App\\Models\\IdeeProjet	15	195	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
178	"Tenetur asperiores architecto quas sapiente laborum commodo incididunt voluptatem"	\N	App\\Models\\IdeeProjet	15	196	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
179	"Et consequatur officia aut exercitation esse consequat Necessitatibus voluptatem"	\N	App\\Models\\IdeeProjet	15	197	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
180	"Sit nisi animi quaerat pariatur"	\N	App\\Models\\IdeeProjet	15	190	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
181	["Eum nemo autem qui quidem sed explicabo Aut non id repudiandae odit tempora anim"]	\N	App\\Models\\IdeeProjet	15	191	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
182	"Corrupti deleniti quibusdam blanditiis est ullamco vel eu sed non ratione"	\N	App\\Models\\IdeeProjet	15	204	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
183	"Et labore temporibus aute velit consequatur similique repellendus Veniam"	\N	App\\Models\\IdeeProjet	15	201	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
184	"Cumque beatae totam quis sunt libero optio"	\N	App\\Models\\IdeeProjet	15	200	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
185	"Cupiditate libero occaecat aut eveniet molestias corporis ipsa accusamus placeat"	\N	App\\Models\\IdeeProjet	15	205	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
186	"Rerum obcaecati nihil temporibus et omnis culpa sapiente consequatur consequatur voluptas ipsum"	\N	App\\Models\\IdeeProjet	15	206	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
187	"Et quaerat consequuntur elit et distinctio Laboris debitis"	\N	App\\Models\\IdeeProjet	15	207	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
188	"Ipsum omnis illo beatae sit consectetur non vitae eum quia reiciendis ut ullamco animi deserunt fugit possimus qui eu"	\N	App\\Models\\IdeeProjet	15	208	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
189	"Facere eaque ut omnis ullam tempora quia quisquam nisi nihil magni quibusdam"	\N	App\\Models\\IdeeProjet	15	202	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
190	"Nemo a unde do nostrud in rerum"	\N	App\\Models\\IdeeProjet	15	203	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
191	"Quae mollitia libero exercitation neque nobis iste eum sit qui id quo unde molestiae fugit molestias pariatur Ullamco aut"	\N	App\\Models\\IdeeProjet	15	198	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
192	"Deserunt ea animi consectetur voluptas eum"	\N	App\\Models\\IdeeProjet	15	199	2025-07-24 20:47:50	2025-07-24 20:47:50	\N
193	"Magni exercitation ea corrupti quisquam molestiae"	\N	App\\Models\\IdeeProjet	16	161	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
194	"Excepturi eiusmod quis culpa sint"	\N	App\\Models\\IdeeProjet	16	162	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
195	1	\N	App\\Models\\IdeeProjet	16	163	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
196	[59]	\N	App\\Models\\IdeeProjet	16	165	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
197	64	\N	App\\Models\\IdeeProjet	16	169	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
198	69	\N	App\\Models\\IdeeProjet	16	170	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
199	{"devise": "FCFA", "montant": 33}	\N	App\\Models\\IdeeProjet	16	166	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
200	1	\N	App\\Models\\IdeeProjet	16	168	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
201	[2]	\N	App\\Models\\IdeeProjet	16	176	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
202	[1]	\N	App\\Models\\IdeeProjet	16	177	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
203	19	\N	App\\Models\\IdeeProjet	16	171	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
204	20	\N	App\\Models\\IdeeProjet	16	172	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
205	27	\N	App\\Models\\IdeeProjet	16	173	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
206	[3]	\N	App\\Models\\IdeeProjet	16	174	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
207	[1]	\N	App\\Models\\IdeeProjet	16	175	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
208	[8]	\N	App\\Models\\IdeeProjet	16	180	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
209	[14]	\N	App\\Models\\IdeeProjet	16	181	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
210	[4]	\N	App\\Models\\IdeeProjet	16	182	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
211	[3]	\N	App\\Models\\IdeeProjet	16	183	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
212	[10]	\N	App\\Models\\IdeeProjet	16	184	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
213	[11]	\N	App\\Models\\IdeeProjet	16	178	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
214	[7]	\N	App\\Models\\IdeeProjet	16	212	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
215	[5]	\N	App\\Models\\IdeeProjet	16	179	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
216	[10]	\N	App\\Models\\IdeeProjet	16	187	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
217	"test"	\N	App\\Models\\IdeeProjet	16	188	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
218	["test"]	\N	App\\Models\\IdeeProjet	16	189	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
219	"test"	\N	App\\Models\\IdeeProjet	16	213	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
220	4	\N	App\\Models\\IdeeProjet	16	185	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
221	5	\N	App\\Models\\IdeeProjet	16	186	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
222	["trezd"]	\N	App\\Models\\IdeeProjet	16	192	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
223	"dfddsd"	\N	App\\Models\\IdeeProjet	16	193	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
224	"dsfds"	\N	App\\Models\\IdeeProjet	16	194	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
225	"sfds"	\N	App\\Models\\IdeeProjet	16	195	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
226	"dfsds"	\N	App\\Models\\IdeeProjet	16	196	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
227	"dfsd"	\N	App\\Models\\IdeeProjet	16	197	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
228	"dsfds"	\N	App\\Models\\IdeeProjet	16	190	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
229	["sfdsd"]	\N	App\\Models\\IdeeProjet	16	191	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
230	"fsdfs"	\N	App\\Models\\IdeeProjet	16	204	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
231	"fsdsd"	\N	App\\Models\\IdeeProjet	16	201	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
232	"dsfds"	\N	App\\Models\\IdeeProjet	16	200	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
233	"dsfds"	\N	App\\Models\\IdeeProjet	16	205	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
234	"fsfds"	\N	App\\Models\\IdeeProjet	16	206	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
236	"fdsf"	\N	App\\Models\\IdeeProjet	16	208	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
237	"dfsfd"	\N	App\\Models\\IdeeProjet	16	202	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
238	"dsfd"	\N	App\\Models\\IdeeProjet	16	203	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
239	"dsfd"	\N	App\\Models\\IdeeProjet	16	198	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
240	"dsfds"	\N	App\\Models\\IdeeProjet	16	199	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
241	"Voluptas reprehenderit aut delectus perferendis expedita enim in possimus voluptatibus consequat Similique eaque omnis"	\N	App\\Models\\IdeeProjet	17	161	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
242	"Aute laboris unde ex ut veniam nihil quidem velit"	\N	App\\Models\\IdeeProjet	17	162	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
243	1	\N	App\\Models\\IdeeProjet	17	163	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
244	[82]	\N	App\\Models\\IdeeProjet	17	165	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
245	6	\N	App\\Models\\IdeeProjet	17	169	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
246	17	\N	App\\Models\\IdeeProjet	17	170	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
247	{"devise": "FCFA", "montant": 74}	\N	App\\Models\\IdeeProjet	17	166	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
248	98	\N	App\\Models\\IdeeProjet	17	168	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
249	[1]	\N	App\\Models\\IdeeProjet	17	176	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
250	[6]	\N	App\\Models\\IdeeProjet	17	177	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
251	19	\N	App\\Models\\IdeeProjet	17	171	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
252	20	\N	App\\Models\\IdeeProjet	17	172	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
253	28	\N	App\\Models\\IdeeProjet	17	173	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
254	[3]	\N	App\\Models\\IdeeProjet	17	174	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
255	[1]	\N	App\\Models\\IdeeProjet	17	175	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
256	[8]	\N	App\\Models\\IdeeProjet	17	180	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
257	[14]	\N	App\\Models\\IdeeProjet	17	181	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
258	[4]	\N	App\\Models\\IdeeProjet	17	182	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
259	[3]	\N	App\\Models\\IdeeProjet	17	183	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
260	[10]	\N	App\\Models\\IdeeProjet	17	184	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
261	[12]	\N	App\\Models\\IdeeProjet	17	178	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
262	[7]	\N	App\\Models\\IdeeProjet	17	212	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
263	[4]	\N	App\\Models\\IdeeProjet	17	179	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
264	[10]	\N	App\\Models\\IdeeProjet	17	187	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
265	"Autem dolor non nisi qui sunt eu ipsum maiores dolor voluptatem dolore"	\N	App\\Models\\IdeeProjet	17	188	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
266	["Provident est ea et sint blanditiis recusandae Accusamus tenetur voluptate"]	\N	App\\Models\\IdeeProjet	17	189	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
267	"Est sed rerum tempora voluptatem Quas atque consectetur proident itaque"	\N	App\\Models\\IdeeProjet	17	213	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
268	4	\N	App\\Models\\IdeeProjet	17	185	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
269	6	\N	App\\Models\\IdeeProjet	17	186	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
270	["Cumque est aute voluptatibus quod minim"]	\N	App\\Models\\IdeeProjet	17	192	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
271	"Odio consequatur Voluptas voluptates in porro excepturi qui dicta non ipsam distinctio Quis ad quasi voluptates dolore"	\N	App\\Models\\IdeeProjet	17	193	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
272	"Ex in autem iusto optio ipsam lorem commodo proident ut perspiciatis rerum dolores"	\N	App\\Models\\IdeeProjet	17	194	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
273	"Fugiat inventore iure eos incidunt blanditiis ipsa consequatur Impedit"	\N	App\\Models\\IdeeProjet	17	195	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
274	"Dolorem ipsum cillum reprehenderit adipisicing et minus accusantium odio mollit aut est qui ut hic tempora amet est"	\N	App\\Models\\IdeeProjet	17	196	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
275	"Est aliquid voluptas facere dolore voluptatibus id rem et do laudantium Nam quam sequi"	\N	App\\Models\\IdeeProjet	17	197	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
276	"Officia quo pariatur Architecto modi culpa enim molestiae minim consequatur Minim"	\N	App\\Models\\IdeeProjet	17	190	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
277	["In adipisicing id eius molestiae consequatur mollit debitis et eos ut expedita magni asperiores hic"]	\N	App\\Models\\IdeeProjet	17	191	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
278	"Modi autem velit est est quam illum dolorem"	\N	App\\Models\\IdeeProjet	17	204	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
279	"Voluptatem quisquam mollitia ea quaerat et"	\N	App\\Models\\IdeeProjet	17	201	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
280	"Dolorum aut dolorum et aut itaque in illum omnis omnis"	\N	App\\Models\\IdeeProjet	17	200	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
281	"Est consequatur quibusdam aliquid amet distinctio Ut soluta voluptate sunt dolores"	\N	App\\Models\\IdeeProjet	17	205	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
282	"Aut et molestiae nostrud rerum officia reiciendis mollitia qui harum voluptates at sit ratione"	\N	App\\Models\\IdeeProjet	17	206	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
283	"Aut adipisicing quod veritatis cum aliquam voluptatem Aut tempor eius blanditiis blanditiis culpa at est eos"	\N	App\\Models\\IdeeProjet	17	207	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
284	"Error perspiciatis quia aut dolore aliquam iure ex obcaecati cumque corporis soluta lorem earum iure"	\N	App\\Models\\IdeeProjet	17	208	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
285	"Deserunt architecto quo voluptate sed error esse natus pariatur Eum exercitationem"	\N	App\\Models\\IdeeProjet	17	202	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
286	"Autem accusantium sit est qui"	\N	App\\Models\\IdeeProjet	17	203	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
287	"Dolorem ex amet eum repudiandae veniam quis error quidem aliquid consectetur ducimus incidunt sit sunt aut quas sunt quisquam dolore"	\N	App\\Models\\IdeeProjet	17	198	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
288	"Veritatis esse fuga Irure iure magna nesciunt ut et qui eos voluptates dolorum hic illum blanditiis non"	\N	App\\Models\\IdeeProjet	17	199	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
340	[87]	\N	App\\Models\\IdeeProjet	20	165	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
289	"Modi laborum Nobis perspiciatis sit totam animi vel aliquid occaecat voluptatem aspernatur quam qui sunt ab in sed reprehenderit voluptatem"	\N	App\\Models\\IdeeProjet	19	161	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
290	"Omnis sit odit optio dolore officiis dolorum est"	\N	App\\Models\\IdeeProjet	19	162	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
291	1	\N	App\\Models\\IdeeProjet	19	163	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
292	[87]	\N	App\\Models\\IdeeProjet	19	165	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
293	10	\N	App\\Models\\IdeeProjet	19	169	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
294	78	\N	App\\Models\\IdeeProjet	19	170	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
295	{"devise": "FCFA", "montant": 68}	\N	App\\Models\\IdeeProjet	19	166	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
296	3	\N	App\\Models\\IdeeProjet	19	168	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
297	[2]	\N	App\\Models\\IdeeProjet	19	176	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
298	[1]	\N	App\\Models\\IdeeProjet	19	177	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
299	19	\N	App\\Models\\IdeeProjet	19	171	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
300	20	\N	App\\Models\\IdeeProjet	19	172	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
301	27	\N	App\\Models\\IdeeProjet	19	173	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
302	[3]	\N	App\\Models\\IdeeProjet	19	174	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
303	[1]	\N	App\\Models\\IdeeProjet	19	175	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
304	[4]	\N	App\\Models\\IdeeProjet	19	182	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
305	[3]	\N	App\\Models\\IdeeProjet	19	183	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
306	[10]	\N	App\\Models\\IdeeProjet	19	184	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
307	[9]	\N	App\\Models\\IdeeProjet	19	178	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
308	[7]	\N	App\\Models\\IdeeProjet	19	212	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
309	[8]	\N	App\\Models\\IdeeProjet	19	180	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
310	[14]	\N	App\\Models\\IdeeProjet	19	181	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
311	[5]	\N	App\\Models\\IdeeProjet	19	179	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
312	["Ipsum voluptas sunt consequat Proident harum explicabo Deserunt accusantium dolor aut fuga Mollit iusto quia distinctio Incididunt unde vel"]	\N	App\\Models\\IdeeProjet	19	189	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
313	"Sint do delectus magnam quisquam labore"	\N	App\\Models\\IdeeProjet	19	213	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
314	[10]	\N	App\\Models\\IdeeProjet	19	187	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
315	"Elit rerum veniam quas quis excepteur sed dolore nihil ullam qui"	\N	App\\Models\\IdeeProjet	19	188	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
316	4	\N	App\\Models\\IdeeProjet	19	185	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
317	6	\N	App\\Models\\IdeeProjet	19	186	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
318	"Aut dolorem do non minus labore illum architecto in dignissimos ea quia qui"	\N	App\\Models\\IdeeProjet	19	196	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
319	"Fuga Voluptas dolor ut et pariatur Libero accusantium nulla magni duis qui nisi laudantium commodo iure incidunt"	\N	App\\Models\\IdeeProjet	19	197	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
320	"Nihil ut ea nihil recusandae Fugiat veritatis dolores nisi veritatis delectus ullamco perferendis do voluptatem Nulla non"	\N	App\\Models\\IdeeProjet	19	190	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
321	["Soluta minim cumque officia non aut ut laborum Nam corrupti quis labore voluptatem Id quia sed"]	\N	App\\Models\\IdeeProjet	19	191	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
322	["Dolor in omnis enim qui sint esse a aliquam deleniti aut magna"]	\N	App\\Models\\IdeeProjet	19	192	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
323	"Temporibus corporis dolor dolores veritatis ea error nisi vel esse occaecat impedit ipsam ut fugiat dolorem sint qui reprehenderit"	\N	App\\Models\\IdeeProjet	19	193	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
324	"Sint odit nobis sunt inventore a amet anim repudiandae dolorem laboris quos et a"	\N	App\\Models\\IdeeProjet	19	194	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
325	"Hic quibusdam qui eaque sapiente sit pariatur Harum non sunt aut perferendis aut commodo quam sint sit dolore cillum"	\N	App\\Models\\IdeeProjet	19	195	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
326	"Dolorum corporis accusamus error cum itaque repellendus Eos atque"	\N	App\\Models\\IdeeProjet	19	204	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
327	"Obcaecati ipsum ad nihil ut commodi dolore in deserunt"	\N	App\\Models\\IdeeProjet	19	201	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
328	"Velit assumenda accusantium eligendi blanditiis id quam"	\N	App\\Models\\IdeeProjet	19	207	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
329	"Voluptatem non voluptatem fugit ullam reprehenderit numquam commodi debitis facere et asperiores esse et"	\N	App\\Models\\IdeeProjet	19	208	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
330	"Dolorem in proident cumque hic sed corrupti obcaecati incidunt magnam vero velit ex sint nostrum"	\N	App\\Models\\IdeeProjet	19	202	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
331	"Suscipit dolores aliquid provident minim dignissimos voluptatem vitae magni itaque"	\N	App\\Models\\IdeeProjet	19	203	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
332	"Voluptates voluptate corrupti quis voluptas qui atque natus non provident eiusmod consequatur Dolore eius veniam molestiae"	\N	App\\Models\\IdeeProjet	19	198	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
333	"Facilis architecto ad consectetur quod tempor corrupti laboriosam optio"	\N	App\\Models\\IdeeProjet	19	200	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
334	"Qui ducimus commodi nisi maxime consequatur veniam nihil"	\N	App\\Models\\IdeeProjet	19	199	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
335	"Iste officia tempore ut beatae et repudiandae aliqua Eu distinctio Ut natus laborum ea deserunt duis"	\N	App\\Models\\IdeeProjet	19	205	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
336	"Sit sed debitis sapiente aut dolore aliquid culpa ab cum maxime quia in quos placeat deleniti"	\N	App\\Models\\IdeeProjet	19	206	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
337	"Modi laborum Nobis pes sit totam animi vel aliquid occaecat voluptatem aspernatur quam qui sunt ab in sed reprehenderit voluptatem"	\N	App\\Models\\IdeeProjet	20	161	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
338	"Omnis sit odit opt dolore officiis dolorum est"	\N	App\\Models\\IdeeProjet	20	162	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
339	1	\N	App\\Models\\IdeeProjet	20	163	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
341	10	\N	App\\Models\\IdeeProjet	20	169	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
342	78	\N	App\\Models\\IdeeProjet	20	170	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
343	{"devise": "FCFA", "montant": 68}	\N	App\\Models\\IdeeProjet	20	166	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
344	3	\N	App\\Models\\IdeeProjet	20	168	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
345	[2]	\N	App\\Models\\IdeeProjet	20	176	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
346	[1]	\N	App\\Models\\IdeeProjet	20	177	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
347	19	\N	App\\Models\\IdeeProjet	20	171	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
348	20	\N	App\\Models\\IdeeProjet	20	172	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
349	27	\N	App\\Models\\IdeeProjet	20	173	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
350	[3]	\N	App\\Models\\IdeeProjet	20	174	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
351	[1]	\N	App\\Models\\IdeeProjet	20	175	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
352	[4]	\N	App\\Models\\IdeeProjet	20	182	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
353	[3]	\N	App\\Models\\IdeeProjet	20	183	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
354	[10]	\N	App\\Models\\IdeeProjet	20	184	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
355	[9]	\N	App\\Models\\IdeeProjet	20	178	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
356	[7]	\N	App\\Models\\IdeeProjet	20	212	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
357	[8]	\N	App\\Models\\IdeeProjet	20	180	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
358	[14]	\N	App\\Models\\IdeeProjet	20	181	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
359	[5]	\N	App\\Models\\IdeeProjet	20	179	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
360	["Ipsum voluptas sunt consequat Proident harum explicabo Deserunt accusantium dolor aut fuga Mollit iusto quia distinctio Incididunt unde vel"]	\N	App\\Models\\IdeeProjet	20	189	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
361	"Sint do delectus magnam quisquam labore"	\N	App\\Models\\IdeeProjet	20	213	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
362	[10]	\N	App\\Models\\IdeeProjet	20	187	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
363	"Elit rerum veniam quas quis excepteur sed dolore nihil ullam qui"	\N	App\\Models\\IdeeProjet	20	188	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
364	4	\N	App\\Models\\IdeeProjet	20	185	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
365	6	\N	App\\Models\\IdeeProjet	20	186	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
366	"Aut dolorem do non minus labore illum architecto in dignissimos ea quia qui"	\N	App\\Models\\IdeeProjet	20	196	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
367	"Fuga Voluptas dolor ut et pariatur Libero accusantium nulla magni duis qui nisi laudantium commodo iure incidunt"	\N	App\\Models\\IdeeProjet	20	197	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
368	"Nihil ut ea nihil recusandae Fugiat veritatis dolores nisi veritatis delectus ullamco perferendis do voluptatem Nulla non"	\N	App\\Models\\IdeeProjet	20	190	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
369	["Soluta minim cumque officia non aut ut laborum Nam corrupti quis labore voluptatem Id quia sed"]	\N	App\\Models\\IdeeProjet	20	191	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
370	["Dolor in omnis enim qui sint esse a aliquam deleniti aut magna"]	\N	App\\Models\\IdeeProjet	20	192	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
371	"Temporibus corporis dolor dolores veritatis ea error nisi vel esse occaecat impedit ipsam ut fugiat dolorem sint qui reprehenderit"	\N	App\\Models\\IdeeProjet	20	193	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
372	"Sint odit nobis sunt inventore a amet anim repudiandae dolorem laboris quos et a"	\N	App\\Models\\IdeeProjet	20	194	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
373	"Hic quibusdam qui eaque sapiente sit pariatur Harum non sunt aut perferendis aut commodo quam sint sit dolore cillum"	\N	App\\Models\\IdeeProjet	20	195	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
374	"Dolorum corporis accusamus error cum itaque repellendus Eos atque"	\N	App\\Models\\IdeeProjet	20	204	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
375	"Obcaecati ipsum ad nihil ut commodi dolore in deserunt"	\N	App\\Models\\IdeeProjet	20	201	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
376	"Velit assumenda accusantium eligendi blanditiis id quam"	\N	App\\Models\\IdeeProjet	20	207	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
377	"Voluptatem non voluptatem fugit ullam reprehenderit numquam commodi debitis facere et asperiores esse et"	\N	App\\Models\\IdeeProjet	20	208	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
378	"Dolorem in proident cumque hic sed corrupti obcaecati incidunt magnam vero velit ex sint nostrum"	\N	App\\Models\\IdeeProjet	20	202	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
379	"Suscipit dolores aliquid provident minim dignissimos voluptatem vitae magni itaque"	\N	App\\Models\\IdeeProjet	20	203	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
380	"Voluptates voluptate corrupti quis voluptas qui atque natus non provident eiusmod consequatur Dolore eius veniam molestiae"	\N	App\\Models\\IdeeProjet	20	198	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
381	"Facilis architecto ad consectetur quod tempor corrupti laboriosam optio"	\N	App\\Models\\IdeeProjet	20	200	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
382	"Qui ducimus commodi nisi maxime consequatur veniam nihil"	\N	App\\Models\\IdeeProjet	20	199	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
383	"Iste officia tempore ut beatae et repudiandae aliqua Eu distinctio Ut natus laborum ea deserunt duis"	\N	App\\Models\\IdeeProjet	20	205	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
384	"Sit sed debitis sapiente aut dolore aliquid culpa ab cum maxime quia in quos placeat deleniti"	\N	App\\Models\\IdeeProjet	20	206	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
385	"Modi laborum No pes sit totam animi vel aliquid occaecat voluptatem aspernatur quam qui sunt ab in sed reprehenderit voluptatem"	\N	App\\Models\\IdeeProjet	21	161	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
386	"Omnis sit odit opt dolore officiis dorum est"	\N	App\\Models\\IdeeProjet	21	162	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
387	1	\N	App\\Models\\IdeeProjet	21	163	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
388	[87]	\N	App\\Models\\IdeeProjet	21	165	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
389	10	\N	App\\Models\\IdeeProjet	21	169	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
390	78	\N	App\\Models\\IdeeProjet	21	170	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
391	{"devise": "FCFA", "montant": 68}	\N	App\\Models\\IdeeProjet	21	166	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
392	3	\N	App\\Models\\IdeeProjet	21	168	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
393	[2]	\N	App\\Models\\IdeeProjet	21	176	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
394	[1]	\N	App\\Models\\IdeeProjet	21	177	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
395	19	\N	App\\Models\\IdeeProjet	21	171	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
396	20	\N	App\\Models\\IdeeProjet	21	172	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
397	27	\N	App\\Models\\IdeeProjet	21	173	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
398	[3]	\N	App\\Models\\IdeeProjet	21	174	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
399	[1]	\N	App\\Models\\IdeeProjet	21	175	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
400	[4]	\N	App\\Models\\IdeeProjet	21	182	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
401	[3]	\N	App\\Models\\IdeeProjet	21	183	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
402	[10]	\N	App\\Models\\IdeeProjet	21	184	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
403	[9]	\N	App\\Models\\IdeeProjet	21	178	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
404	[7]	\N	App\\Models\\IdeeProjet	21	212	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
405	[8]	\N	App\\Models\\IdeeProjet	21	180	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
406	[14]	\N	App\\Models\\IdeeProjet	21	181	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
407	[5]	\N	App\\Models\\IdeeProjet	21	179	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
408	["Ipsum voluptas sunt consequat Proident harum explicabo Deserunt accusantium dolor aut fuga Mollit iusto quia distinctio Incididunt unde vel"]	\N	App\\Models\\IdeeProjet	21	189	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
409	"Sint do delectus magnam quisquam labore"	\N	App\\Models\\IdeeProjet	21	213	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
410	[10]	\N	App\\Models\\IdeeProjet	21	187	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
411	"Elit rerum veniam quas quis excepteur sed dolore nihil ullam qui"	\N	App\\Models\\IdeeProjet	21	188	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
412	4	\N	App\\Models\\IdeeProjet	21	185	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
413	6	\N	App\\Models\\IdeeProjet	21	186	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
414	"Aut dolorem do non minus labore illum architecto in dignissimos ea quia qui"	\N	App\\Models\\IdeeProjet	21	196	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
415	"Fuga Voluptas dolor ut et pariatur Libero accusantium nulla magni duis qui nisi laudantium commodo iure incidunt"	\N	App\\Models\\IdeeProjet	21	197	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
416	"Nihil ut ea nihil recusandae Fugiat veritatis dolores nisi veritatis delectus ullamco perferendis do voluptatem Nulla non"	\N	App\\Models\\IdeeProjet	21	190	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
417	["Soluta minim cumque officia non aut ut laborum Nam corrupti quis labore voluptatem Id quia sed"]	\N	App\\Models\\IdeeProjet	21	191	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
418	["Dolor in omnis enim qui sint esse a aliquam deleniti aut magna"]	\N	App\\Models\\IdeeProjet	21	192	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
419	"Temporibus corporis dolor dolores veritatis ea error nisi vel esse occaecat impedit ipsam ut fugiat dolorem sint qui reprehenderit"	\N	App\\Models\\IdeeProjet	21	193	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
420	"Sint odit nobis sunt inventore a amet anim repudiandae dolorem laboris quos et a"	\N	App\\Models\\IdeeProjet	21	194	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
421	"Hic quibusdam qui eaque sapiente sit pariatur Harum non sunt aut perferendis aut commodo quam sint sit dolore cillum"	\N	App\\Models\\IdeeProjet	21	195	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
422	"Dolorum corporis accusamus error cum itaque repellendus Eos atque"	\N	App\\Models\\IdeeProjet	21	204	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
423	"Obcaecati ipsum ad nihil ut commodi dolore in deserunt"	\N	App\\Models\\IdeeProjet	21	201	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
424	"Velit assumenda accusantium eligendi blanditiis id quam"	\N	App\\Models\\IdeeProjet	21	207	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
425	"Voluptatem non voluptatem fugit ullam reprehenderit numquam commodi debitis facere et asperiores esse et"	\N	App\\Models\\IdeeProjet	21	208	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
426	"Dolorem in proident cumque hic sed corrupti obcaecati incidunt magnam vero velit ex sint nostrum"	\N	App\\Models\\IdeeProjet	21	202	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
427	"Suscipit dolores aliquid provident minim dignissimos voluptatem vitae magni itaque"	\N	App\\Models\\IdeeProjet	21	203	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
428	"Voluptates voluptate corrupti quis voluptas qui atque natus non provident eiusmod consequatur Dolore eius veniam molestiae"	\N	App\\Models\\IdeeProjet	21	198	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
429	"Facilis architecto ad consectetur quod tempor corrupti laboriosam optio"	\N	App\\Models\\IdeeProjet	21	200	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
430	"Qui ducimus commodi nisi maxime consequatur veniam nihil"	\N	App\\Models\\IdeeProjet	21	199	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
431	"Iste officia tempore ut beatae et repudiandae aliqua Eu distinctio Ut natus laborum ea deserunt duis"	\N	App\\Models\\IdeeProjet	21	205	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
432	"Sit sed debitis sapiente aut dolore aliquid culpa ab cum maxime quia in quos placeat deleniti"	\N	App\\Models\\IdeeProjet	21	206	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
433	"Sit ex ad modi expedita sunt eiusmod laudantium quod dolor sed ut aut proident"	\N	App\\Models\\IdeeProjet	26	161	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
434	"Sapiente aliquid in vitae laborum Explicabo Ut a"	\N	App\\Models\\IdeeProjet	26	162	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
435	1	\N	App\\Models\\IdeeProjet	26	163	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
436	[67]	\N	App\\Models\\IdeeProjet	26	165	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
437	92	\N	App\\Models\\IdeeProjet	26	169	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
438	16	\N	App\\Models\\IdeeProjet	26	170	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
439	{"devise": "FCFA", "montant": 78}	\N	App\\Models\\IdeeProjet	26	166	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
440	78	\N	App\\Models\\IdeeProjet	26	168	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
441	[2]	\N	App\\Models\\IdeeProjet	26	176	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
442	[1]	\N	App\\Models\\IdeeProjet	26	177	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
443	19	\N	App\\Models\\IdeeProjet	26	171	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
444	20	\N	App\\Models\\IdeeProjet	26	172	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
445	28	\N	App\\Models\\IdeeProjet	26	173	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
446	[3]	\N	App\\Models\\IdeeProjet	26	174	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
447	[1]	\N	App\\Models\\IdeeProjet	26	175	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
448	[4]	\N	App\\Models\\IdeeProjet	26	182	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
449	[3]	\N	App\\Models\\IdeeProjet	26	183	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
450	[10]	\N	App\\Models\\IdeeProjet	26	184	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
451	[9]	\N	App\\Models\\IdeeProjet	26	178	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
452	[7]	\N	App\\Models\\IdeeProjet	26	212	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
453	[8]	\N	App\\Models\\IdeeProjet	26	180	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
454	[14]	\N	App\\Models\\IdeeProjet	26	181	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
455	[4]	\N	App\\Models\\IdeeProjet	26	179	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
456	["Numquam aut quia quae totam culpa"]	\N	App\\Models\\IdeeProjet	26	189	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
457	"Non aperiam sint consequatur Nihil qui"	\N	App\\Models\\IdeeProjet	26	213	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
458	[10]	\N	App\\Models\\IdeeProjet	26	187	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
459	"Voluptate ipsum quis eveniet quo aliquid voluptatem quibusdam sint occaecat qui"	\N	App\\Models\\IdeeProjet	26	188	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
460	4	\N	App\\Models\\IdeeProjet	26	185	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
461	6	\N	App\\Models\\IdeeProjet	26	186	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
462	"Et vero officia natus et itaque blanditiis culpa in aut sit"	\N	App\\Models\\IdeeProjet	26	196	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
463	"Ipsum sit ullamco nulla duis id cupiditate dolorem quos nemo eiusmod totam quidem autem ullam consectetur ad exercitationem"	\N	App\\Models\\IdeeProjet	26	197	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
464	"Ex labore error est incidunt quas esse a sit rerum facilis possimus soluta assumenda et ducimus Nam"	\N	App\\Models\\IdeeProjet	26	190	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
465	["Irure quaerat suscipit maxime harum pariatur Laudantium deserunt vero"]	\N	App\\Models\\IdeeProjet	26	191	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
466	["Aspernatur esse et temporibus et eos distinctio Amet Nam libero itaque"]	\N	App\\Models\\IdeeProjet	26	192	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
467	"Nisi vero laboris minima voluptatibus tempora voluptates vero vel aute aliquip"	\N	App\\Models\\IdeeProjet	26	193	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
468	"Velit excepteur nisi lorem eiusmod placeat odit facilis excepteur in"	\N	App\\Models\\IdeeProjet	26	194	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
469	"Aut ullam vel magna minus quos excepteur amet enim"	\N	App\\Models\\IdeeProjet	26	195	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
470	"Est rem error quia aut"	\N	App\\Models\\IdeeProjet	26	204	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
471	"Est dicta laborum a fuga Modi quas vero"	\N	App\\Models\\IdeeProjet	26	201	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
472	"Fugiat consequatur lorem aut sint sit id ipsa doloribus veniam provident et ut nostrum sint"	\N	App\\Models\\IdeeProjet	26	207	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
473	"Autem consequuntur suscipit sunt distinctio Est consequatur natus vero anim saepe sint mollitia modi quis officia impedit aperiam debitis ullamco"	\N	App\\Models\\IdeeProjet	26	208	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
474	"Ut quo minus illum occaecat sint excepturi est fugiat quo vel porro officia laudantium qui omnis reiciendis"	\N	App\\Models\\IdeeProjet	26	202	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
475	"Recusandae Aut dignissimos delectus cillum ipsum commodi"	\N	App\\Models\\IdeeProjet	26	203	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
476	"Ut aute quia est quidem inventore esse eiusmod impedit"	\N	App\\Models\\IdeeProjet	26	198	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
477	"Reiciendis in aliqua Quia quis anim voluptatem voluptas et cupidatat excepturi amet quis veniam consectetur et vero Nam velit"	\N	App\\Models\\IdeeProjet	26	200	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
478	"Dolores dolorum expedita aut dicta corrupti consectetur accusamus proident sapiente ab nisi nisi non distinctio"	\N	App\\Models\\IdeeProjet	26	199	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
479	"Debitis est fuga Tenetur cupidatat maiores illum laboris asperiores voluptatem est sit tempore consectetur nostrud nemo"	\N	App\\Models\\IdeeProjet	26	205	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
480	"Et omnis deleniti laborum Nulla ut et"	\N	App\\Models\\IdeeProjet	26	206	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
481	"Delectus quis ratione quae est voluptatum excepturi quis porro odit"	\N	App\\Models\\IdeeProjet	27	161	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
482	"Cum nisi rerum facilis ipsam maiores rerum veritat"	\N	App\\Models\\IdeeProjet	27	162	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
483	1	\N	App\\Models\\IdeeProjet	27	163	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
484	[86]	\N	App\\Models\\IdeeProjet	27	165	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
485	70	\N	App\\Models\\IdeeProjet	27	169	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
486	28	\N	App\\Models\\IdeeProjet	27	170	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
487	{"devise": "FCFA", "montant": 56}	\N	App\\Models\\IdeeProjet	27	166	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
488	13	\N	App\\Models\\IdeeProjet	27	168	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
489	19	\N	App\\Models\\IdeeProjet	27	171	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
490	20	\N	App\\Models\\IdeeProjet	27	172	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
491	27	\N	App\\Models\\IdeeProjet	27	173	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
492	[3]	\N	App\\Models\\IdeeProjet	27	174	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
493	[1]	\N	App\\Models\\IdeeProjet	27	175	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
494	[1]	\N	App\\Models\\IdeeProjet	27	176	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
495	[6]	\N	App\\Models\\IdeeProjet	27	177	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
496	[9]	\N	App\\Models\\IdeeProjet	27	178	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
497	[4]	\N	App\\Models\\IdeeProjet	27	179	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
498	[8]	\N	App\\Models\\IdeeProjet	27	180	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
499	[14]	\N	App\\Models\\IdeeProjet	27	181	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
500	[4]	\N	App\\Models\\IdeeProjet	27	182	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
501	[7]	\N	App\\Models\\IdeeProjet	27	212	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
502	[3]	\N	App\\Models\\IdeeProjet	27	183	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
503	[10]	\N	App\\Models\\IdeeProjet	27	184	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
504	4	\N	App\\Models\\IdeeProjet	27	185	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
505	6	\N	App\\Models\\IdeeProjet	27	186	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
506	[10]	\N	App\\Models\\IdeeProjet	27	187	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
507	"Perferendis adipisicing ex rerum aliquam duis non sed sunt"	\N	App\\Models\\IdeeProjet	27	188	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
508	"Delectus tempora et unde iste suscipit laborum qui nemo"	\N	App\\Models\\IdeeProjet	27	213	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
509	["Exercitationem et omnis veniam minus vel debitis impedit sint tempore magni aute omnis veniam adipisicing quis et dolore consequatur"]	\N	App\\Models\\IdeeProjet	27	189	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
510	"Dolore in laudantium eiusmod magnam"	\N	App\\Models\\IdeeProjet	27	190	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
511	["Eligendi in sed dolores voluptatem corporis"]	\N	App\\Models\\IdeeProjet	27	191	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
512	["Cupiditate est saepe quis tenetur quod fugit perferendis similique dolorum veniam voluptatem Nam"]	\N	App\\Models\\IdeeProjet	27	192	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
513	"Cupidatat consequatur ut in voluptatum quibusdam enim quidem magnam mollit consequatur do consequat Praesentium incidunt voluptas cumque corporis"	\N	App\\Models\\IdeeProjet	27	193	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
514	"Duis fugit minim ea autem numquam hic occaecat temporibus in fugiat non fuga"	\N	App\\Models\\IdeeProjet	27	194	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
515	"Et et in eos est voluptatem Ut aut aut blanditiis voluptate voluptatum magnam est omnis unde quibusdam"	\N	App\\Models\\IdeeProjet	27	195	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
516	"Qui natus cupidatat exercitation id ipsum cum earum magni ut"	\N	App\\Models\\IdeeProjet	27	196	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
517	"Quis alias necessitatibus perspiciatis error fugiat facilis"	\N	App\\Models\\IdeeProjet	27	197	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
518	"Nihil recusandae Sed id magna rerum et reiciendis in veniam aliquip esse"	\N	App\\Models\\IdeeProjet	27	198	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
519	"Quo atque vel itaque illo voluptatum est adipisci vero qui enim aut nemo"	\N	App\\Models\\IdeeProjet	27	199	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
520	"Officia sed hic atque sed commodo autem sapiente illum quo ut aliqua Ab"	\N	App\\Models\\IdeeProjet	27	200	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
521	"Irure id laudantium est illo enim ut omnis cum consectetur ut et voluptatibus quia dolore et at corrupti reprehenderit"	\N	App\\Models\\IdeeProjet	27	201	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
522	"Dolore labore delectus quis magnam rem elit velit consequatur lorem suscipit dolor"	\N	App\\Models\\IdeeProjet	27	202	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
523	"Ut ex mollitia nihil velit anim vel"	\N	App\\Models\\IdeeProjet	27	204	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
524	"Dolorem placeat blanditiis laborum maxime beatae dolor eius quaerat sapiente corrupti necessitatibus et atque"	\N	App\\Models\\IdeeProjet	27	203	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
525	"Sit laboris explicabo Atque excepturi quam pariatur Rerum amet omnis est ut labore mollitia suscipit autem"	\N	App\\Models\\IdeeProjet	27	205	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
526	"Magnam aperiam explicabo Unde non itaque sed quas"	\N	App\\Models\\IdeeProjet	27	208	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
527	"Earum eos cumque necessitatibus cillum perferendis adipisicing ullam aperiam ullam maxime sit voluptas iusto"	\N	App\\Models\\IdeeProjet	27	207	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
528	"Non voluptate autem officia lorem recusandae Laboriosam laborum qui amet nobis et dignissimos"	\N	App\\Models\\IdeeProjet	27	206	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
529	"Rerum alias asperiores culpa temporibus consequatur Provident pariatur Numquam nesciunt aute enim et voluptatem aut ipsum omnis"	\N	App\\Models\\IdeeProjet	31	161	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
530	"Aliquid facilis nisi voluptas aliquam consequatur"	\N	App\\Models\\IdeeProjet	31	162	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
531	1	\N	App\\Models\\IdeeProjet	31	163	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
532	[93]	\N	App\\Models\\IdeeProjet	31	165	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
533	32	\N	App\\Models\\IdeeProjet	31	169	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
534	60	\N	App\\Models\\IdeeProjet	31	170	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
535	{"devise": "FCFA", "montant": 60}	\N	App\\Models\\IdeeProjet	31	166	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
536	98	\N	App\\Models\\IdeeProjet	31	168	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
537	19	\N	App\\Models\\IdeeProjet	31	171	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
538	20	\N	App\\Models\\IdeeProjet	31	172	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
539	28	\N	App\\Models\\IdeeProjet	31	173	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
540	[3]	\N	App\\Models\\IdeeProjet	31	174	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
541	[1]	\N	App\\Models\\IdeeProjet	31	175	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
542	[1]	\N	App\\Models\\IdeeProjet	31	176	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
543	[6]	\N	App\\Models\\IdeeProjet	31	177	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
544	[10]	\N	App\\Models\\IdeeProjet	31	178	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
545	[5]	\N	App\\Models\\IdeeProjet	31	179	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
546	[8]	\N	App\\Models\\IdeeProjet	31	180	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
547	[14]	\N	App\\Models\\IdeeProjet	31	181	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
548	[4]	\N	App\\Models\\IdeeProjet	31	182	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
549	[7]	\N	App\\Models\\IdeeProjet	31	212	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
550	[3]	\N	App\\Models\\IdeeProjet	31	183	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
551	[10]	\N	App\\Models\\IdeeProjet	31	184	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
552	4	\N	App\\Models\\IdeeProjet	31	185	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
553	6	\N	App\\Models\\IdeeProjet	31	186	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
554	[14]	\N	App\\Models\\IdeeProjet	31	187	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
555	"Quia ut est sit ut ut ad facilis mollitia enim nobis"	\N	App\\Models\\IdeeProjet	31	188	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
556	"Porro tempore necessitatibus impedit dolorem pariatur Sunt sapiente culpa fugit dolore vel quasi"	\N	App\\Models\\IdeeProjet	31	213	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
557	["Incididunt et officia ut nisi nihil in"]	\N	App\\Models\\IdeeProjet	31	189	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
558	"Possimus a fugit dolor ut non cumque nisi excepteur in voluptas sed et ipsum quasi iusto anim accusamus voluptates eaque"	\N	App\\Models\\IdeeProjet	31	190	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
559	["Natus earum sint cillum sint sint rerum id rem ut voluptatibus aliquid dolore perspiciatis delectus accusamus ipsam iure perspiciatis"]	\N	App\\Models\\IdeeProjet	31	191	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
560	["Rerum officia Nam distinctio Temporibus consequuntur et inventore labore sequi aut"]	\N	App\\Models\\IdeeProjet	31	192	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
561	"Perferendis molestiae accusamus qui quidem officiis totam laudantium excepturi inventore aut saepe"	\N	App\\Models\\IdeeProjet	31	193	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
562	"Suscipit irure veniam fugiat sunt cillum sit pariatur Dolores ex"	\N	App\\Models\\IdeeProjet	31	194	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
563	"Sit consequat Ut animi adipisci odio similique quo iure dicta ex"	\N	App\\Models\\IdeeProjet	31	195	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
564	"Ratione labore dignissimos repellendus Ut lorem reiciendis"	\N	App\\Models\\IdeeProjet	31	196	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
565	"Obcaecati non elit elit placeat reiciendis quia ullam voluptatem fugiat anim odio eius iste"	\N	App\\Models\\IdeeProjet	31	197	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
566	"Eum architecto sit tempore ut"	\N	App\\Models\\IdeeProjet	31	198	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
567	"Qui cillum maiores dicta eum vel exercitation dolorem"	\N	App\\Models\\IdeeProjet	31	199	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
568	"Quasi omnis doloribus eos sed mollit quibusdam deleniti beatae eius id in ad id"	\N	App\\Models\\IdeeProjet	31	200	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
569	"Earum amet magni culpa animi"	\N	App\\Models\\IdeeProjet	31	201	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
570	"Consectetur quidem cupidatat nisi aperiam culpa aliquip laboris rerum dolor id minim non voluptatem Aliqua"	\N	App\\Models\\IdeeProjet	31	202	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
571	"Dolor ducimus sit tempor illum architecto officiis quibusdam"	\N	App\\Models\\IdeeProjet	31	204	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
572	"Ut dolores beatae sunt commodi cillum veniam et aut nesciunt nulla ab minus et doloremque doloribus quis"	\N	App\\Models\\IdeeProjet	31	203	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
573	"Dolorem dolore aliquid quibusdam saepe quae dolor quos perferendis est ut fugiat in reprehenderit consequatur facilis sed dolorem saepe sit"	\N	App\\Models\\IdeeProjet	31	205	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
574	"Quia alias nihil est voluptate dolore cum aut praesentium pariatur Placeat"	\N	App\\Models\\IdeeProjet	31	208	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
575	"Voluptatem tempor minus sed est id repellendus Minima laboriosam animi blanditiis iste voluptate quod"	\N	App\\Models\\IdeeProjet	31	207	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
576	"Voluptas quia iusto ea laboris ea ea quisquam aliquam molestiae nesciunt accusantium lorem"	\N	App\\Models\\IdeeProjet	31	206	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
577	"Et autem mollit dolore voluptatibus id quod ad aut suscipit esse"	\N	App\\Models\\IdeeProjet	32	161	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
578	"Eius et irure natus nostrum sit laboris voluptas "	\N	App\\Models\\IdeeProjet	32	162	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
579	1	\N	App\\Models\\IdeeProjet	32	163	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
580	[77]	\N	App\\Models\\IdeeProjet	32	165	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
581	0	\N	App\\Models\\IdeeProjet	32	169	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
582	9	\N	App\\Models\\IdeeProjet	32	170	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
583	{"devise": "FCFA", "montant": 86}	\N	App\\Models\\IdeeProjet	32	166	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
584	60	\N	App\\Models\\IdeeProjet	32	168	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
585	19	\N	App\\Models\\IdeeProjet	32	171	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
586	20	\N	App\\Models\\IdeeProjet	32	172	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
587	28	\N	App\\Models\\IdeeProjet	32	173	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
588	[3]	\N	App\\Models\\IdeeProjet	32	174	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
589	[1]	\N	App\\Models\\IdeeProjet	32	175	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
590	[1]	\N	App\\Models\\IdeeProjet	32	176	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
591	[6]	\N	App\\Models\\IdeeProjet	32	177	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
592	[9]	\N	App\\Models\\IdeeProjet	32	178	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
593	[5]	\N	App\\Models\\IdeeProjet	32	179	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
594	[8]	\N	App\\Models\\IdeeProjet	32	180	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
595	[14]	\N	App\\Models\\IdeeProjet	32	181	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
596	[4]	\N	App\\Models\\IdeeProjet	32	182	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
597	[7]	\N	App\\Models\\IdeeProjet	32	212	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
598	[3]	\N	App\\Models\\IdeeProjet	32	183	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
599	[10]	\N	App\\Models\\IdeeProjet	32	184	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
600	4	\N	App\\Models\\IdeeProjet	32	185	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
601	6	\N	App\\Models\\IdeeProjet	32	186	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
602	[14]	\N	App\\Models\\IdeeProjet	32	187	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
603	"Earum molestiae aut esse dolor sint dolorem"	\N	App\\Models\\IdeeProjet	32	188	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
604	"Ex ullam voluptate rem eligendi in quibusdam iste reprehenderit at"	\N	App\\Models\\IdeeProjet	32	213	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
605	["Non placeat ea enim eos minima ut non culpa voluptatem pariatur Reiciendis fugiat"]	\N	App\\Models\\IdeeProjet	32	189	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
606	"Dolor anim totam consectetur qui quam est vero perspiciatis velit quas nesciunt molestiae minus ut"	\N	App\\Models\\IdeeProjet	32	190	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
607	["Voluptatum et provident eum magnam fugiat id rerum laboris ad omnis adipisci"]	\N	App\\Models\\IdeeProjet	32	191	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
608	["Non enim molestiae voluptate exercitation quia exercitation"]	\N	App\\Models\\IdeeProjet	32	192	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
609	"Non laboriosam reiciendis provident in debitis facilis id odio quaerat dolore quos non accusantium aut eum aperiam eum fugiat"	\N	App\\Models\\IdeeProjet	32	193	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
610	"Suscipit cum non et perspiciatis accusamus nihil ullam et alias veniam nostrud non sint incidunt Nam mollitia exercitation eos ut"	\N	App\\Models\\IdeeProjet	32	194	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
611	"Libero repellendus Id veniam tempora nisi nihil excepteur vitae lorem voluptatum autem dolores"	\N	App\\Models\\IdeeProjet	32	195	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
612	"Omnis velit quis sed voluptas suscipit est doloribus non do eiusmod voluptatem"	\N	App\\Models\\IdeeProjet	32	196	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
613	"Cupiditate ipsum id eaque ex incidunt earum inventore ipsum ullamco quae qui omnis aspernatur quo"	\N	App\\Models\\IdeeProjet	32	197	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
614	"Ratione possimus ut optio provident nemo qui elit in nisi voluptate amet nostrum"	\N	App\\Models\\IdeeProjet	32	198	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
615	"Ipsam non sed lorem amet pariatur Rerum et provident est consequatur Numquam nostrud veritatis architecto similique consectetur qui"	\N	App\\Models\\IdeeProjet	32	199	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
616	"Enim consequuntur deserunt quo illum doloremque ut culpa labore sapiente exercitationem"	\N	App\\Models\\IdeeProjet	32	200	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
617	"Magnam necessitatibus nostrud anim molestiae molestias qui est et sit aliquid est officiis ut ipsum"	\N	App\\Models\\IdeeProjet	32	201	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
618	"Nulla sint molestiae voluptate aliqua Animi excepturi duis reprehenderit et dolor at omnis qui"	\N	App\\Models\\IdeeProjet	32	202	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
619	"Sint molestias occaecat ut modi doloremque ut occaecat aliquid est"	\N	App\\Models\\IdeeProjet	32	204	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
620	"Officia sit nobis sunt aut et quod in aut quisquam id consectetur et ut"	\N	App\\Models\\IdeeProjet	32	203	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
621	"Nobis laborum id dolores numquam tenetur quo suscipit autem asperiores dolorem commodi consequat In illo incididunt"	\N	App\\Models\\IdeeProjet	32	205	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
622	"Ut nostrum ut doloribus nihil"	\N	App\\Models\\IdeeProjet	32	208	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
623	"Quia id id aut perspiciatis sapiente ad qui nihil est quis in commodo deserunt possimus totam qui esse ducimus minima"	\N	App\\Models\\IdeeProjet	32	207	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
624	"Incidunt aliqua Voluptate sint rerum"	\N	App\\Models\\IdeeProjet	32	206	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
625	"Sit quae anim optio illo ad molestias quibusdam reprehenderit ea nihil incidunt non asperiores"	\N	App\\Models\\IdeeProjet	33	161	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
626	"Iusto neque eius cupiditate nihil saepe ea rem ut "	\N	App\\Models\\IdeeProjet	33	162	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
627	1	\N	App\\Models\\IdeeProjet	33	163	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
628	[59]	\N	App\\Models\\IdeeProjet	33	165	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
629	41	\N	App\\Models\\IdeeProjet	33	169	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
630	2	\N	App\\Models\\IdeeProjet	33	170	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
631	{"devise": "FCFA", "montant": 95}	\N	App\\Models\\IdeeProjet	33	166	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
632	35	\N	App\\Models\\IdeeProjet	33	168	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
633	19	\N	App\\Models\\IdeeProjet	33	171	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
634	20	\N	App\\Models\\IdeeProjet	33	172	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
635	27	\N	App\\Models\\IdeeProjet	33	173	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
636	[3]	\N	App\\Models\\IdeeProjet	33	174	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
637	[1]	\N	App\\Models\\IdeeProjet	33	175	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
638	[2]	\N	App\\Models\\IdeeProjet	33	176	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
639	[1]	\N	App\\Models\\IdeeProjet	33	177	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
640	[7]	\N	App\\Models\\IdeeProjet	33	178	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
641	[5]	\N	App\\Models\\IdeeProjet	33	179	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
642	[8]	\N	App\\Models\\IdeeProjet	33	180	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
643	[14]	\N	App\\Models\\IdeeProjet	33	181	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
644	[4]	\N	App\\Models\\IdeeProjet	33	182	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
645	[7]	\N	App\\Models\\IdeeProjet	33	212	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
646	[3]	\N	App\\Models\\IdeeProjet	33	183	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
647	[10]	\N	App\\Models\\IdeeProjet	33	184	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
648	4	\N	App\\Models\\IdeeProjet	33	185	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
649	6	\N	App\\Models\\IdeeProjet	33	186	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
650	[14]	\N	App\\Models\\IdeeProjet	33	187	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
651	"Deleniti eiusmod ex architecto duis"	\N	App\\Models\\IdeeProjet	33	188	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
652	"Totam atque unde qui cupiditate in atque quam veritatis non sint officia"	\N	App\\Models\\IdeeProjet	33	213	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
653	["Voluptatibus impedit ducimus saepe obcaecati ea nobis et sit possimus labore autem magnam"]	\N	App\\Models\\IdeeProjet	33	189	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
654	"Cupiditate nostrud eum do duis reiciendis quam porro aliquam aliquam optio quasi temporibus cillum voluptatem quas harum quod officia iusto"	\N	App\\Models\\IdeeProjet	33	190	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
655	["Excepturi reiciendis dicta nihil neque ad placeat fuga Eiusmod omnis quibusdam explicabo Quia eos ipsam est cumque deserunt"]	\N	App\\Models\\IdeeProjet	33	191	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
656	["Esse perferendis eos ea quod eaque magni sit excepturi a delectus dolorem ipsam repudiandae laborum velit"]	\N	App\\Models\\IdeeProjet	33	192	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
965	10	\N	App\\Models\\IdeeProjet	40	169	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
657	"Id laudantium est quod explicabo Animi fuga Sit ipsum architecto magni quo ratione quam odit"	\N	App\\Models\\IdeeProjet	33	193	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
658	"Neque praesentium quasi labore anim sit lorem ex id esse esse quaerat nostrud ea eveniet est"	\N	App\\Models\\IdeeProjet	33	194	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
659	"Velit sunt nisi ex animi ex libero aut atque"	\N	App\\Models\\IdeeProjet	33	195	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
660	"Maiores quos minima blanditiis voluptas sit"	\N	App\\Models\\IdeeProjet	33	196	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
661	"Quos nulla tempore sint consequatur eu obcaecati voluptatem"	\N	App\\Models\\IdeeProjet	33	197	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
662	"Reprehenderit ea ipsum ut ad rem soluta excepturi velit voluptatem dolor cupiditate aspernatur vitae quia"	\N	App\\Models\\IdeeProjet	33	198	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
663	"Praesentium enim eos repellendus Explicabo Dignissimos in ut Nam ipsum nesciunt"	\N	App\\Models\\IdeeProjet	33	199	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
664	"Expedita magna tenetur quisquam in eaque amet corrupti vel laudantium ullam magna et hic autem cum dolor"	\N	App\\Models\\IdeeProjet	33	200	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
665	"Ex mollit facere laboris id cumque est facilis"	\N	App\\Models\\IdeeProjet	33	201	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
666	"Adipisci doloribus dolor esse fuga Ut rerum"	\N	App\\Models\\IdeeProjet	33	202	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
667	"Dolorum commodo enim alias et quasi nisi maiores ut quae ullamco quis voluptatem est consequatur"	\N	App\\Models\\IdeeProjet	33	204	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
668	"Reprehenderit qui consequatur Ratione nobis cumque temporibus cillum"	\N	App\\Models\\IdeeProjet	33	203	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
669	"Dignissimos consequatur distinctio Sint doloremque modi eum consequatur voluptatem sed"	\N	App\\Models\\IdeeProjet	33	205	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
670	"Quam dolor amet et cillum officia quia eum sed consequatur"	\N	App\\Models\\IdeeProjet	33	208	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
671	"Tempora sed ex nobis vel dolore occaecat molestiae dolorum officia possimus do"	\N	App\\Models\\IdeeProjet	33	207	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
672	"Et fuga In fugiat fugiat rerum elit"	\N	App\\Models\\IdeeProjet	33	206	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
673	"Fuga Pariatur Blanditiis irure qui sint anim reprehenderit commodi necessitatibus commodi numquam minima quas magna ad modi veniam aut"	\N	App\\Models\\IdeeProjet	34	161	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
674	"Id do suscipit repellendus Magnam autem placeat "	\N	App\\Models\\IdeeProjet	34	162	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
675	1	\N	App\\Models\\IdeeProjet	34	163	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
676	[70]	\N	App\\Models\\IdeeProjet	34	165	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
677	75	\N	App\\Models\\IdeeProjet	34	169	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
678	15	\N	App\\Models\\IdeeProjet	34	170	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
679	{"devise": "FCFA", "montant": 69}	\N	App\\Models\\IdeeProjet	34	166	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
680	30	\N	App\\Models\\IdeeProjet	34	168	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
681	19	\N	App\\Models\\IdeeProjet	34	171	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
682	20	\N	App\\Models\\IdeeProjet	34	172	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
683	27	\N	App\\Models\\IdeeProjet	34	173	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
684	[3]	\N	App\\Models\\IdeeProjet	34	174	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
685	[1]	\N	App\\Models\\IdeeProjet	34	175	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
686	[2]	\N	App\\Models\\IdeeProjet	34	176	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
687	[1]	\N	App\\Models\\IdeeProjet	34	177	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
688	[10]	\N	App\\Models\\IdeeProjet	34	178	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
689	[5]	\N	App\\Models\\IdeeProjet	34	179	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
690	[8]	\N	App\\Models\\IdeeProjet	34	180	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
691	[14]	\N	App\\Models\\IdeeProjet	34	181	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
692	[4]	\N	App\\Models\\IdeeProjet	34	182	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
693	[7]	\N	App\\Models\\IdeeProjet	34	212	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
694	[3]	\N	App\\Models\\IdeeProjet	34	183	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
695	[10]	\N	App\\Models\\IdeeProjet	34	184	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
696	4	\N	App\\Models\\IdeeProjet	34	185	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
697	6	\N	App\\Models\\IdeeProjet	34	186	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
698	[10]	\N	App\\Models\\IdeeProjet	34	187	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
699	"Dolores inventore impedit necessitatibus voluptatibus earum nulla nisi est in porro aut non non culpa iste nulla"	\N	App\\Models\\IdeeProjet	34	188	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
700	"Sint quaerat nulla est laborum eaque esse nihil eos rerum veniam vel"	\N	App\\Models\\IdeeProjet	34	213	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
701	["Ratione consequat Eligendi sint et tempore iste voluptatibus lorem atque quasi eligendi eveniet pariatur Dolore sint est minus adipisicing obcaecati"]	\N	App\\Models\\IdeeProjet	34	189	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
702	"Magni quibusdam recusandae Occaecat quia ullam ab modi veniam"	\N	App\\Models\\IdeeProjet	34	190	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
703	["Beatae et facilis quis ipsum sunt illum officiis est voluptate suscipit veniam"]	\N	App\\Models\\IdeeProjet	34	191	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
704	["Aut provident quo rem voluptas nihil molestias distinctio Quia dolor voluptate duis ex commodo reprehenderit"]	\N	App\\Models\\IdeeProjet	34	192	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
705	"Dolorum ea ipsum quis porro modi ducimus"	\N	App\\Models\\IdeeProjet	34	193	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
706	"Neque beatae beatae labore velit velit odio ut dolor aut aspernatur ipsa ut sit fugiat adipisicing provident natus cupiditate quas"	\N	App\\Models\\IdeeProjet	34	194	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
707	"Asperiores velit pariatur Voluptates non aut velit sed saepe"	\N	App\\Models\\IdeeProjet	34	195	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
708	"Laboriosam do animi dolor asperiores adipisicing illo sed in voluptas officia laboris sed culpa nesciunt veniam"	\N	App\\Models\\IdeeProjet	34	196	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
709	"Distinctio Minus in enim dolor consectetur aspernatur commodi perspiciatis"	\N	App\\Models\\IdeeProjet	34	197	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
710	"Vel exercitationem optio eos mollit at ipsa omnis recusandae"	\N	App\\Models\\IdeeProjet	34	198	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
711	"Laudantium esse et tenetur aliqua Molestias est qui nesciunt est"	\N	App\\Models\\IdeeProjet	34	199	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
712	"Eum molestias rerum culpa deleniti eum tempor ea cillum labore"	\N	App\\Models\\IdeeProjet	34	200	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
713	"Totam sed est tempora Nam quasi libero reprehenderit veritatis nisi voluptate quis cum illum accusamus tempor voluptatem Temporibus"	\N	App\\Models\\IdeeProjet	34	201	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
714	"Aute autem perspiciatis officia alias commodo aut voluptatem"	\N	App\\Models\\IdeeProjet	34	202	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
715	"Irure nostrum est dolor quisquam ipsam qui nihil sed in proident vel autem dolore excepturi beatae"	\N	App\\Models\\IdeeProjet	34	204	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
716	"Quo soluta laborum Ipsam et ipsum elit libero enim ad est dolore nisi officia"	\N	App\\Models\\IdeeProjet	34	203	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
717	"Ut soluta aut ut dolor totam deserunt esse incidunt laboriosam temporibus culpa quisquam proident"	\N	App\\Models\\IdeeProjet	34	205	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
718	"Rem recusandae Quae quo excepteur reprehenderit aliqua Officia"	\N	App\\Models\\IdeeProjet	34	208	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
719	"Amet aperiam et dolore natus aute ut dolor laudantium sunt ut lorem ullam quis alias voluptate sed"	\N	App\\Models\\IdeeProjet	34	207	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
720	"Sed pariatur Lorem laboriosam excepteur quae voluptatem voluptate dicta perferendis repellendus Asperiores pariatur In et sit quaerat labore"	\N	App\\Models\\IdeeProjet	34	206	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
721	"Voluptate qui cum qui aliquam velit"	\N	App\\Models\\IdeeProjet	35	161	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
722	"Quo ex laborum quo nostrum dolor autem et minim si"	\N	App\\Models\\IdeeProjet	35	162	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
723	1	\N	App\\Models\\IdeeProjet	35	163	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
724	[66]	\N	App\\Models\\IdeeProjet	35	165	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
725	18	\N	App\\Models\\IdeeProjet	35	169	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
726	92	\N	App\\Models\\IdeeProjet	35	170	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
727	{"devise": "FCFA", "montant": 39}	\N	App\\Models\\IdeeProjet	35	166	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
728	84	\N	App\\Models\\IdeeProjet	35	168	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
729	19	\N	App\\Models\\IdeeProjet	35	171	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
730	20	\N	App\\Models\\IdeeProjet	35	172	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
731	27	\N	App\\Models\\IdeeProjet	35	173	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
732	[3]	\N	App\\Models\\IdeeProjet	35	174	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
733	[1]	\N	App\\Models\\IdeeProjet	35	175	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
734	[2]	\N	App\\Models\\IdeeProjet	35	176	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
735	[1]	\N	App\\Models\\IdeeProjet	35	177	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
736	[10]	\N	App\\Models\\IdeeProjet	35	178	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
737	[4]	\N	App\\Models\\IdeeProjet	35	179	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
738	[8]	\N	App\\Models\\IdeeProjet	35	180	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
739	[14]	\N	App\\Models\\IdeeProjet	35	181	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
740	[4]	\N	App\\Models\\IdeeProjet	35	182	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
741	[7]	\N	App\\Models\\IdeeProjet	35	212	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
742	[3]	\N	App\\Models\\IdeeProjet	35	183	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
743	[10]	\N	App\\Models\\IdeeProjet	35	184	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
744	4	\N	App\\Models\\IdeeProjet	35	185	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
745	6	\N	App\\Models\\IdeeProjet	35	186	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
746	[14]	\N	App\\Models\\IdeeProjet	35	187	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
747	"Ipsam nihil deserunt iure architecto consequatur dolor"	\N	App\\Models\\IdeeProjet	35	188	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
748	"Dignissimos et dolorem nobis numquam nisi tempor ut expedita quis odit repudiandae qui voluptas in rem ipsum culpa omnis"	\N	App\\Models\\IdeeProjet	35	213	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
749	["Ipsum possimus sint quia ea"]	\N	App\\Models\\IdeeProjet	35	189	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
750	"Dolorem ipsum quia perferendis porro non"	\N	App\\Models\\IdeeProjet	35	190	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
751	["Non ut tempore eum sunt modi nihil necessitatibus iste asperiores consectetur et"]	\N	App\\Models\\IdeeProjet	35	191	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
752	["Expedita labore maxime labore ut reprehenderit ad at qui facere numquam magna officia dolorem nemo nulla ex quia laboris cupidatat"]	\N	App\\Models\\IdeeProjet	35	192	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
753	"Adipisicing elit incidunt sequi dolor ut maiores ut qui explicabo Quo consequatur sint voluptatem aut eos modi deserunt adipisci"	\N	App\\Models\\IdeeProjet	35	193	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
754	"Quidem reprehenderit quaerat et quo"	\N	App\\Models\\IdeeProjet	35	194	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
755	"Est et est in eos nesciunt sit ipsum qui deleniti quas fuga In nisi ut quis"	\N	App\\Models\\IdeeProjet	35	195	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
756	"Laborum Placeat repellendus Est quod"	\N	App\\Models\\IdeeProjet	35	196	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
757	"Aliquid ad fugiat error enim labore et dignissimos neque nisi deleniti"	\N	App\\Models\\IdeeProjet	35	197	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
758	"Assumenda odit modi quos incidunt ipsum perspiciatis rerum obcaecati aut reiciendis"	\N	App\\Models\\IdeeProjet	35	198	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
759	"Aut harum proident odit quo excepteur tempor ducimus impedit quam"	\N	App\\Models\\IdeeProjet	35	199	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
760	"Asperiores eos voluptas quia repellendus Ex"	\N	App\\Models\\IdeeProjet	35	200	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
1383	\N	\N	App\\Models\\IdeeProjet	61	195	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
761	"Dolorem voluptatum ut est placeat fugiat sint corrupti laboriosam reiciendis enim similique"	\N	App\\Models\\IdeeProjet	35	201	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
762	"Eiusmod quia quia ex aspernatur ut et ipsam iste error consectetur"	\N	App\\Models\\IdeeProjet	35	202	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
763	"Illum ut incidunt consequatur occaecat excepturi aut eligendi voluptatem excepturi hic error officiis"	\N	App\\Models\\IdeeProjet	35	204	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
764	"Non aut eaque tempore itaque deserunt commodi amet"	\N	App\\Models\\IdeeProjet	35	203	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
765	"Quo et totam lorem repudiandae officia cupidatat est"	\N	App\\Models\\IdeeProjet	35	205	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
766	"Amet corrupti labore incididunt nihil optio et aliquip"	\N	App\\Models\\IdeeProjet	35	208	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
767	"Culpa incidunt porro voluptatibus iure veniam sunt"	\N	App\\Models\\IdeeProjet	35	207	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
768	"Mollitia tenetur dolor consequatur et reprehenderit modi vero quas dignissimos mollitia"	\N	App\\Models\\IdeeProjet	35	206	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
769	"Modi laborum No pes sit totam animi vel aliquid occaecat voluptatem aspernatur quam qui sunt ab in sed reprehenderit voluptatem"	\N	App\\Models\\IdeeProjet	36	161	2025-07-25 16:51:49	2025-07-25 16:51:49	\N
770	"Omnis sit odit opt dolore officiis dorum est"	\N	App\\Models\\IdeeProjet	36	162	2025-07-25 16:51:49	2025-07-25 16:51:49	\N
771	1	\N	App\\Models\\IdeeProjet	36	163	2025-07-25 16:51:49	2025-07-25 16:51:49	\N
772	[87]	\N	App\\Models\\IdeeProjet	36	165	2025-07-25 16:51:49	2025-07-25 16:51:49	\N
773	10	\N	App\\Models\\IdeeProjet	36	169	2025-07-25 16:51:49	2025-07-25 16:51:49	\N
774	78	\N	App\\Models\\IdeeProjet	36	170	2025-07-25 16:51:49	2025-07-25 16:51:49	\N
775	{"devise": "FCFA", "montant": 68}	\N	App\\Models\\IdeeProjet	36	166	2025-07-25 16:51:49	2025-07-25 16:51:49	\N
776	3	\N	App\\Models\\IdeeProjet	36	168	2025-07-25 16:51:49	2025-07-25 16:51:49	\N
777	[2]	\N	App\\Models\\IdeeProjet	36	176	2025-07-25 16:51:49	2025-07-25 16:51:49	\N
778	[1]	\N	App\\Models\\IdeeProjet	36	177	2025-07-25 16:51:49	2025-07-25 16:51:49	\N
779	19	\N	App\\Models\\IdeeProjet	36	171	2025-07-25 16:51:49	2025-07-25 16:51:49	\N
780	20	\N	App\\Models\\IdeeProjet	36	172	2025-07-25 16:51:49	2025-07-25 16:51:49	\N
781	27	\N	App\\Models\\IdeeProjet	36	173	2025-07-25 16:51:49	2025-07-25 16:51:49	\N
782	[3]	\N	App\\Models\\IdeeProjet	36	174	2025-07-25 16:51:49	2025-07-25 16:51:49	\N
783	[1]	\N	App\\Models\\IdeeProjet	36	175	2025-07-25 16:51:49	2025-07-25 16:51:49	\N
784	[4]	\N	App\\Models\\IdeeProjet	36	182	2025-07-25 16:51:49	2025-07-25 16:51:49	\N
785	[3]	\N	App\\Models\\IdeeProjet	36	183	2025-07-25 16:51:49	2025-07-25 16:51:49	\N
786	[10]	\N	App\\Models\\IdeeProjet	36	184	2025-07-25 16:51:49	2025-07-25 16:51:49	\N
787	[9]	\N	App\\Models\\IdeeProjet	36	178	2025-07-25 16:51:49	2025-07-25 16:51:49	\N
788	[7]	\N	App\\Models\\IdeeProjet	36	212	2025-07-25 16:51:50	2025-07-25 16:51:50	\N
789	[8]	\N	App\\Models\\IdeeProjet	36	180	2025-07-25 16:51:50	2025-07-25 16:51:50	\N
790	[14]	\N	App\\Models\\IdeeProjet	36	181	2025-07-25 16:51:50	2025-07-25 16:51:50	\N
791	[5]	\N	App\\Models\\IdeeProjet	36	179	2025-07-25 16:51:50	2025-07-25 16:51:50	\N
792	["Ipsum voluptas sunt consequat Proident harum explicabo Deserunt accusantium dolor aut fuga Mollit iusto quia distinctio Incididunt unde vel"]	\N	App\\Models\\IdeeProjet	36	189	2025-07-25 16:51:50	2025-07-25 16:51:50	\N
793	"Sint do delectus magnam quisquam labore"	\N	App\\Models\\IdeeProjet	36	213	2025-07-25 16:51:50	2025-07-25 16:51:50	\N
794	[10]	\N	App\\Models\\IdeeProjet	36	187	2025-07-25 16:51:50	2025-07-25 16:51:50	\N
795	"Elit rerum veniam quas quis excepteur sed dolore nihil ullam qui"	\N	App\\Models\\IdeeProjet	36	188	2025-07-25 16:51:50	2025-07-25 16:51:50	\N
796	4	\N	App\\Models\\IdeeProjet	36	185	2025-07-25 16:51:50	2025-07-25 16:51:50	\N
797	6	\N	App\\Models\\IdeeProjet	36	186	2025-07-25 16:51:50	2025-07-25 16:51:50	\N
798	"Aut dolorem do non minus labore illum architecto in dignissimos ea quia qui"	\N	App\\Models\\IdeeProjet	36	196	2025-07-25 16:51:50	2025-07-25 16:51:50	\N
799	"Fuga Voluptas dolor ut et pariatur Libero accusantium nulla magni duis qui nisi laudantium commodo iure incidunt"	\N	App\\Models\\IdeeProjet	36	197	2025-07-25 16:51:50	2025-07-25 16:51:50	\N
800	"Nihil ut ea nihil recusandae Fugiat veritatis dolores nisi veritatis delectus ullamco perferendis do voluptatem Nulla non"	\N	App\\Models\\IdeeProjet	36	190	2025-07-25 16:51:50	2025-07-25 16:51:50	\N
801	["Soluta minim cumque officia non aut ut laborum Nam corrupti quis labore voluptatem Id quia sed"]	\N	App\\Models\\IdeeProjet	36	191	2025-07-25 16:51:50	2025-07-25 16:51:50	\N
802	["Dolor in omnis enim qui sint esse a aliquam deleniti aut magna"]	\N	App\\Models\\IdeeProjet	36	192	2025-07-25 16:51:50	2025-07-25 16:51:50	\N
803	"Temporibus corporis dolor dolores veritatis ea error nisi vel esse occaecat impedit ipsam ut fugiat dolorem sint qui reprehenderit"	\N	App\\Models\\IdeeProjet	36	193	2025-07-25 16:51:50	2025-07-25 16:51:50	\N
804	"Sint odit nobis sunt inventore a amet anim repudiandae dolorem laboris quos et a"	\N	App\\Models\\IdeeProjet	36	194	2025-07-25 16:51:50	2025-07-25 16:51:50	\N
805	"Hic quibusdam qui eaque sapiente sit pariatur Harum non sunt aut perferendis aut commodo quam sint sit dolore cillum"	\N	App\\Models\\IdeeProjet	36	195	2025-07-25 16:51:50	2025-07-25 16:51:50	\N
806	"Dolorum corporis accusamus error cum itaque repellendus Eos atque"	\N	App\\Models\\IdeeProjet	36	204	2025-07-25 16:51:50	2025-07-25 16:51:50	\N
807	"Obcaecati ipsum ad nihil ut commodi dolore in deserunt"	\N	App\\Models\\IdeeProjet	36	201	2025-07-25 16:51:50	2025-07-25 16:51:50	\N
808	"Velit assumenda accusantium eligendi blanditiis id quam"	\N	App\\Models\\IdeeProjet	36	207	2025-07-25 16:51:50	2025-07-25 16:51:50	\N
809	"Voluptatem non voluptatem fugit ullam reprehenderit numquam commodi debitis facere et asperiores esse et"	\N	App\\Models\\IdeeProjet	36	208	2025-07-25 16:51:50	2025-07-25 16:51:50	\N
810	"Dolorem in proident cumque hic sed corrupti obcaecati incidunt magnam vero velit ex sint nostrum"	\N	App\\Models\\IdeeProjet	36	202	2025-07-25 16:51:50	2025-07-25 16:51:50	\N
811	"Suscipit dolores aliquid provident minim dignissimos voluptatem vitae magni itaque"	\N	App\\Models\\IdeeProjet	36	203	2025-07-25 16:51:50	2025-07-25 16:51:50	\N
966	78	\N	App\\Models\\IdeeProjet	40	170	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
812	"Voluptates voluptate corrupti quis voluptas qui atque natus non provident eiusmod consequatur Dolore eius veniam molestiae"	\N	App\\Models\\IdeeProjet	36	198	2025-07-25 16:51:50	2025-07-25 16:51:50	\N
813	"Facilis architecto ad consectetur quod tempor corrupti laboriosam optio"	\N	App\\Models\\IdeeProjet	36	200	2025-07-25 16:51:50	2025-07-25 16:51:50	\N
814	"Qui ducimus commodi nisi maxime consequatur veniam nihil"	\N	App\\Models\\IdeeProjet	36	199	2025-07-25 16:51:50	2025-07-25 16:51:50	\N
815	"Iste officia tempore ut beatae et repudiandae aliqua Eu distinctio Ut natus laborum ea deserunt duis"	\N	App\\Models\\IdeeProjet	36	205	2025-07-25 16:51:50	2025-07-25 16:51:50	\N
816	"Sit sed debitis sapiente aut dolore aliquid culpa ab cum maxime quia in quos placeat deleniti"	\N	App\\Models\\IdeeProjet	36	206	2025-07-25 16:51:50	2025-07-25 16:51:50	\N
817	"Modi labs sit totam animi vel aliquid occaecat voluptatem aspernatur quam qui sunt ab in sed reprehenderit voluptatem"	\N	App\\Models\\IdeeProjet	37	161	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
818	"Omnis sit odit opt dolore ofs dorum est"	\N	App\\Models\\IdeeProjet	37	162	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
819	1	\N	App\\Models\\IdeeProjet	37	163	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
820	[87]	\N	App\\Models\\IdeeProjet	37	165	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
821	10	\N	App\\Models\\IdeeProjet	37	169	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
822	78	\N	App\\Models\\IdeeProjet	37	170	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
823	{"devise": "FCFA", "montant": 68}	\N	App\\Models\\IdeeProjet	37	166	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
824	3	\N	App\\Models\\IdeeProjet	37	168	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
825	[2]	\N	App\\Models\\IdeeProjet	37	176	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
826	[1]	\N	App\\Models\\IdeeProjet	37	177	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
827	19	\N	App\\Models\\IdeeProjet	37	171	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
828	20	\N	App\\Models\\IdeeProjet	37	172	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
829	27	\N	App\\Models\\IdeeProjet	37	173	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
830	[3]	\N	App\\Models\\IdeeProjet	37	174	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
831	[1]	\N	App\\Models\\IdeeProjet	37	175	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
832	[4]	\N	App\\Models\\IdeeProjet	37	182	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
833	[3]	\N	App\\Models\\IdeeProjet	37	183	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
834	[10]	\N	App\\Models\\IdeeProjet	37	184	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
835	[9]	\N	App\\Models\\IdeeProjet	37	178	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
836	[7]	\N	App\\Models\\IdeeProjet	37	212	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
837	[8]	\N	App\\Models\\IdeeProjet	37	180	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
838	[14]	\N	App\\Models\\IdeeProjet	37	181	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
839	[5]	\N	App\\Models\\IdeeProjet	37	179	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
840	["Ipsum voluptas sunt consequat Proident harum explicabo Deserunt accusantium dolor aut fuga Mollit iusto quia distinctio Incididunt unde vel"]	\N	App\\Models\\IdeeProjet	37	189	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
841	"Sint do delectus magnam quisquam labore"	\N	App\\Models\\IdeeProjet	37	213	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
842	[10]	\N	App\\Models\\IdeeProjet	37	187	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
843	"Elit rerum veniam quas quis excepteur sed dolore nihil ullam qui"	\N	App\\Models\\IdeeProjet	37	188	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
844	4	\N	App\\Models\\IdeeProjet	37	185	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
845	6	\N	App\\Models\\IdeeProjet	37	186	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
846	"Aut dolorem do non minus labore illum architecto in dignissimos ea quia qui"	\N	App\\Models\\IdeeProjet	37	196	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
847	"Fuga Voluptas dolor ut et pariatur Libero accusantium nulla magni duis qui nisi laudantium commodo iure incidunt"	\N	App\\Models\\IdeeProjet	37	197	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
848	"Nihil ut ea nihil recusandae Fugiat veritatis dolores nisi veritatis delectus ullamco perferendis do voluptatem Nulla non"	\N	App\\Models\\IdeeProjet	37	190	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
849	["Soluta minim cumque officia non aut ut laborum Nam corrupti quis labore voluptatem Id quia sed"]	\N	App\\Models\\IdeeProjet	37	191	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
850	["Dolor in omnis enim qui sint esse a aliquam deleniti aut magna"]	\N	App\\Models\\IdeeProjet	37	192	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
851	"Temporibus corporis dolor dolores veritatis ea error nisi vel esse occaecat impedit ipsam ut fugiat dolorem sint qui reprehenderit"	\N	App\\Models\\IdeeProjet	37	193	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
852	"Sint odit nobis sunt inventore a amet anim repudiandae dolorem laboris quos et a"	\N	App\\Models\\IdeeProjet	37	194	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
853	"Hic quibusdam qui eaque sapiente sit pariatur Harum non sunt aut perferendis aut commodo quam sint sit dolore cillum"	\N	App\\Models\\IdeeProjet	37	195	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
854	"Dolorum corporis accusamus error cum itaque repellendus Eos atque"	\N	App\\Models\\IdeeProjet	37	204	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
855	"Obcaecati ipsum ad nihil ut commodi dolore in deserunt"	\N	App\\Models\\IdeeProjet	37	201	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
856	"Velit assumenda accusantium eligendi blanditiis id quam"	\N	App\\Models\\IdeeProjet	37	207	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
857	"Voluptatem non voluptatem fugit ullam reprehenderit numquam commodi debitis facere et asperiores esse et"	\N	App\\Models\\IdeeProjet	37	208	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
858	"Dolorem in proident cumque hic sed corrupti obcaecati incidunt magnam vero velit ex sint nostrum"	\N	App\\Models\\IdeeProjet	37	202	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
859	"Suscipit dolores aliquid provident minim dignissimos voluptatem vitae magni itaque"	\N	App\\Models\\IdeeProjet	37	203	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
860	"Voluptates voluptate corrupti quis voluptas qui atque natus non provident eiusmod consequatur Dolore eius veniam molestiae"	\N	App\\Models\\IdeeProjet	37	198	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
861	"Facilis architecto ad consectetur quod tempor corrupti laboriosam optio"	\N	App\\Models\\IdeeProjet	37	200	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
862	"Qui ducimus commodi nisi maxime consequatur veniam nihil"	\N	App\\Models\\IdeeProjet	37	199	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
863	"Iste officia tempore ut beatae et repudiandae aliqua Eu distinctio Ut natus laborum ea deserunt duis"	\N	App\\Models\\IdeeProjet	37	205	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
864	"Sit sed debitis sapiente aut dolore aliquid culpa ab cum maxime quia in quos placeat deleniti"	\N	App\\Models\\IdeeProjet	37	206	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
865	"Vero autem quasi quia consectetur mollitia amet alias deleniti"	\N	App\\Models\\IdeeProjet	38	161	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
866	"Ea quam deserunt nulla fugiat ad magnam velit nemo"	\N	App\\Models\\IdeeProjet	38	162	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
867	1	\N	App\\Models\\IdeeProjet	38	163	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
868	[61]	\N	App\\Models\\IdeeProjet	38	165	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
869	53	\N	App\\Models\\IdeeProjet	38	169	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
870	89	\N	App\\Models\\IdeeProjet	38	170	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
871	{"devise": "FCFA", "montant": 20}	\N	App\\Models\\IdeeProjet	38	166	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
872	7	\N	App\\Models\\IdeeProjet	38	168	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
873	19	\N	App\\Models\\IdeeProjet	38	171	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
874	20	\N	App\\Models\\IdeeProjet	38	172	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
875	27	\N	App\\Models\\IdeeProjet	38	173	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
876	[3]	\N	App\\Models\\IdeeProjet	38	174	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
877	[1]	\N	App\\Models\\IdeeProjet	38	175	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
878	[2]	\N	App\\Models\\IdeeProjet	38	176	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
879	[1]	\N	App\\Models\\IdeeProjet	38	177	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
880	[12]	\N	App\\Models\\IdeeProjet	38	178	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
881	[5]	\N	App\\Models\\IdeeProjet	38	179	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
882	[8]	\N	App\\Models\\IdeeProjet	38	180	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
883	[14]	\N	App\\Models\\IdeeProjet	38	181	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
884	[4]	\N	App\\Models\\IdeeProjet	38	182	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
885	[7]	\N	App\\Models\\IdeeProjet	38	212	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
886	[3]	\N	App\\Models\\IdeeProjet	38	183	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
887	[10]	\N	App\\Models\\IdeeProjet	38	184	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
888	4	\N	App\\Models\\IdeeProjet	38	185	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
889	6	\N	App\\Models\\IdeeProjet	38	186	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
890	[14]	\N	App\\Models\\IdeeProjet	38	187	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
891	"Iure nulla alias reprehenderit minim dolores sapiente porro ut inventore quas duis rerum rerum accusantium"	\N	App\\Models\\IdeeProjet	38	188	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
892	"Vel maiores voluptatem Ipsum blanditiis enim ad nostrud id est in ea alias dolorem ex sunt dolor nulla vel"	\N	App\\Models\\IdeeProjet	38	213	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
893	["Aut autem qui sit sit deleniti illum voluptate ut voluptas"]	\N	App\\Models\\IdeeProjet	38	189	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
894	"Aut omnis repudiandae autem sint ut alias deleniti necessitatibus quaerat qui eum quia provident fugit"	\N	App\\Models\\IdeeProjet	38	190	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
895	["Dicta ab quia voluptatum soluta eum"]	\N	App\\Models\\IdeeProjet	38	191	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
896	["Animi fugiat mollit est fugit expedita sit iure dolorem repudiandae dolorem tempore"]	\N	App\\Models\\IdeeProjet	38	192	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
897	"Tempor tempore inventore sunt ex asperiores dignissimos voluptatum ad accusantium exercitation ut soluta ab deserunt dolores voluptatum laudantium"	\N	App\\Models\\IdeeProjet	38	193	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
898	"Fuga Dolores pariatur Beatae tempora consectetur necessitatibus repudiandae repudiandae nobis voluptate eu assumenda a obcaecati optio impedit tempore"	\N	App\\Models\\IdeeProjet	38	194	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
899	"Officia natus quidem molestias facere perferendis tempore ab adipisci delectus"	\N	App\\Models\\IdeeProjet	38	195	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
900	"Fugiat deleniti magnam ullam reprehenderit in et id voluptatum id exercitationem voluptatem dolor est irure qui voluptas facilis"	\N	App\\Models\\IdeeProjet	38	196	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
901	"Elit aut molestiae facilis molestiae"	\N	App\\Models\\IdeeProjet	38	197	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
902	"Elit iusto corporis eius sed ea atque placeat rerum distinctio Ducimus optio eos fugiat dicta libero reprehenderit est"	\N	App\\Models\\IdeeProjet	38	198	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
903	"Facilis consequatur blanditiis nemo obcaecati magna qui et qui dignissimos nesciunt animi atque dolor"	\N	App\\Models\\IdeeProjet	38	199	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
904	"Dolorem suscipit facilis est officiis velit error ut voluptatem quia non illum Nam ex dolor fuga Qui laboris incididunt"	\N	App\\Models\\IdeeProjet	38	200	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
905	"Cillum ab consequatur Temporibus quia"	\N	App\\Models\\IdeeProjet	38	201	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
906	"Possimus consequat Expedita aut consectetur anim animi consequatur optio"	\N	App\\Models\\IdeeProjet	38	202	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
907	"Nihil aspernatur eius sit quidem duis qui"	\N	App\\Models\\IdeeProjet	38	204	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
908	"Eligendi ex facere proident eius autem incididunt dignissimos id esse"	\N	App\\Models\\IdeeProjet	38	203	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
909	"Aliquam magnam dolorem mollitia inventore excepteur veniam harum et ad aliquip esse lorem tempor cupidatat laborum iste harum saepe consequatur"	\N	App\\Models\\IdeeProjet	38	205	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
910	"Aut assumenda impedit excepturi ut laboriosam"	\N	App\\Models\\IdeeProjet	38	208	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
911	"Occaecat ea quam voluptas odio excepturi animi impedit aliquam ad et dicta tempor esse quo aliquip at non earum"	\N	App\\Models\\IdeeProjet	38	207	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
912	"Ea id voluptas aut nobis amet labore pariatur Rerum dolores voluptate minima ipsum qui ratione porro laborum Est quo facere"	\N	App\\Models\\IdeeProjet	38	206	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
1384	\N	\N	App\\Models\\IdeeProjet	61	196	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
913	"Modi labs sittam animi vel aliquid occaecat voluptatem aspernatur quam qui sunt ab in sed reprehenderit voluptatem"	\N	App\\Models\\IdeeProjet	39	161	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
914	"Omnis sit odit opt doklore ofs dorum est"	\N	App\\Models\\IdeeProjet	39	162	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
915	1	\N	App\\Models\\IdeeProjet	39	163	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
916	[87]	\N	App\\Models\\IdeeProjet	39	165	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
917	10	\N	App\\Models\\IdeeProjet	39	169	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
918	78	\N	App\\Models\\IdeeProjet	39	170	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
919	{"devise": "FCFA", "montant": 68}	\N	App\\Models\\IdeeProjet	39	166	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
920	3	\N	App\\Models\\IdeeProjet	39	168	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
921	[2]	\N	App\\Models\\IdeeProjet	39	176	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
922	[1]	\N	App\\Models\\IdeeProjet	39	177	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
923	19	\N	App\\Models\\IdeeProjet	39	171	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
924	20	\N	App\\Models\\IdeeProjet	39	172	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
925	27	\N	App\\Models\\IdeeProjet	39	173	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
926	[3]	\N	App\\Models\\IdeeProjet	39	174	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
927	[1]	\N	App\\Models\\IdeeProjet	39	175	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
928	[4]	\N	App\\Models\\IdeeProjet	39	182	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
929	[3]	\N	App\\Models\\IdeeProjet	39	183	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
930	[10]	\N	App\\Models\\IdeeProjet	39	184	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
931	[9]	\N	App\\Models\\IdeeProjet	39	178	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
932	[7]	\N	App\\Models\\IdeeProjet	39	212	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
933	[8]	\N	App\\Models\\IdeeProjet	39	180	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
934	[14]	\N	App\\Models\\IdeeProjet	39	181	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
935	[5]	\N	App\\Models\\IdeeProjet	39	179	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
936	["Ipsum voluptas sunt consequat Proident harum explicabo Deserunt accusantium dolor aut fuga Mollit iusto quia distinctio Incididunt unde vel"]	\N	App\\Models\\IdeeProjet	39	189	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
937	"Sint do delectus magnam quisquam labore"	\N	App\\Models\\IdeeProjet	39	213	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
938	[10]	\N	App\\Models\\IdeeProjet	39	187	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
939	"Elit rerum veniam quas quis excepteur sed dolore nihil ullam qui"	\N	App\\Models\\IdeeProjet	39	188	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
940	4	\N	App\\Models\\IdeeProjet	39	185	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
941	6	\N	App\\Models\\IdeeProjet	39	186	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
942	"Aut dolorem do non minus labore illum architecto in dignissimos ea quia qui"	\N	App\\Models\\IdeeProjet	39	196	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
943	"Fuga Voluptas dolor ut et pariatur Libero accusantium nulla magni duis qui nisi laudantium commodo iure incidunt"	\N	App\\Models\\IdeeProjet	39	197	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
944	"Nihil ut ea nihil recusandae Fugiat veritatis dolores nisi veritatis delectus ullamco perferendis do voluptatem Nulla non"	\N	App\\Models\\IdeeProjet	39	190	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
945	["Soluta minim cumque officia non aut ut laborum Nam corrupti quis labore voluptatem Id quia sed"]	\N	App\\Models\\IdeeProjet	39	191	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
946	["Dolor in omnis enim qui sint esse a aliquam deleniti aut magna"]	\N	App\\Models\\IdeeProjet	39	192	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
947	"Temporibus corporis dolor dolores veritatis ea error nisi vel esse occaecat impedit ipsam ut fugiat dolorem sint qui reprehenderit"	\N	App\\Models\\IdeeProjet	39	193	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
948	"Sint odit nobis sunt inventore a amet anim repudiandae dolorem laboris quos et a"	\N	App\\Models\\IdeeProjet	39	194	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
949	"Hic quibusdam qui eaque sapiente sit pariatur Harum non sunt aut perferendis aut commodo quam sint sit dolore cillum"	\N	App\\Models\\IdeeProjet	39	195	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
950	"Dolorum corporis accusamus error cum itaque repellendus Eos atque"	\N	App\\Models\\IdeeProjet	39	204	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
951	"Obcaecati ipsum ad nihil ut commodi dolore in deserunt"	\N	App\\Models\\IdeeProjet	39	201	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
952	"Velit assumenda accusantium eligendi blanditiis id quam"	\N	App\\Models\\IdeeProjet	39	207	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
953	"Voluptatem non voluptatem fugit ullam reprehenderit numquam commodi debitis facere et asperiores esse et"	\N	App\\Models\\IdeeProjet	39	208	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
954	"Dolorem in proident cumque hic sed corrupti obcaecati incidunt magnam vero velit ex sint nostrum"	\N	App\\Models\\IdeeProjet	39	202	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
955	"Suscipit dolores aliquid provident minim dignissimos voluptatem vitae magni itaque"	\N	App\\Models\\IdeeProjet	39	203	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
956	"Voluptates voluptate corrupti quis voluptas qui atque natus non provident eiusmod consequatur Dolore eius veniam molestiae"	\N	App\\Models\\IdeeProjet	39	198	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
957	"Facilis architecto ad consectetur quod tempor corrupti laboriosam optio"	\N	App\\Models\\IdeeProjet	39	200	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
958	"Qui ducimus commodi nisi maxime consequatur veniam nihil"	\N	App\\Models\\IdeeProjet	39	199	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
959	"Iste officia tempore ut beatae et repudiandae aliqua Eu distinctio Ut natus laborum ea deserunt duis"	\N	App\\Models\\IdeeProjet	39	205	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
960	"Sit sed debitis sapiente aut dolore aliquid culpa ab cum maxime quia in quos placeat deleniti"	\N	App\\Models\\IdeeProjet	39	206	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
961	"Modi labs sit vel aliquid occaecat voluptatem aspernatur quam qui sunt ab in sed reprehenderit voluptatem"	\N	App\\Models\\IdeeProjet	40	161	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
962	"Omnis sit odit opt doklore ofs dorumjhgf est"	\N	App\\Models\\IdeeProjet	40	162	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
963	1	\N	App\\Models\\IdeeProjet	40	163	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
964	[87]	\N	App\\Models\\IdeeProjet	40	165	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
967	{"devise": "FCFA", "montant": 68}	\N	App\\Models\\IdeeProjet	40	166	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
968	3	\N	App\\Models\\IdeeProjet	40	168	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
969	[2]	\N	App\\Models\\IdeeProjet	40	176	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
970	[1]	\N	App\\Models\\IdeeProjet	40	177	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
971	19	\N	App\\Models\\IdeeProjet	40	171	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
972	20	\N	App\\Models\\IdeeProjet	40	172	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
973	27	\N	App\\Models\\IdeeProjet	40	173	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
974	[3]	\N	App\\Models\\IdeeProjet	40	174	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
975	[1]	\N	App\\Models\\IdeeProjet	40	175	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
976	[4]	\N	App\\Models\\IdeeProjet	40	182	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
977	[3]	\N	App\\Models\\IdeeProjet	40	183	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
978	[10]	\N	App\\Models\\IdeeProjet	40	184	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
979	[9]	\N	App\\Models\\IdeeProjet	40	178	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
980	[7]	\N	App\\Models\\IdeeProjet	40	212	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
981	[8]	\N	App\\Models\\IdeeProjet	40	180	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
982	[14]	\N	App\\Models\\IdeeProjet	40	181	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
983	[5]	\N	App\\Models\\IdeeProjet	40	179	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
984	["Ipsum voluptas sunt consequat Proident harum explicabo Deserunt accusantium dolor aut fuga Mollit iusto quia distinctio Incididunt unde vel"]	\N	App\\Models\\IdeeProjet	40	189	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
985	"Sint do delectus magnam quisquam labore"	\N	App\\Models\\IdeeProjet	40	213	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
986	[10]	\N	App\\Models\\IdeeProjet	40	187	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
987	"Elit rerum veniam quas quis excepteur sed dolore nihil ullam qui"	\N	App\\Models\\IdeeProjet	40	188	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
988	4	\N	App\\Models\\IdeeProjet	40	185	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
989	6	\N	App\\Models\\IdeeProjet	40	186	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
990	"Aut dolorem do non minus labore illum architecto in dignissimos ea quia qui"	\N	App\\Models\\IdeeProjet	40	196	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
991	"Fuga Voluptas dolor ut et pariatur Libero accusantium nulla magni duis qui nisi laudantium commodo iure incidunt"	\N	App\\Models\\IdeeProjet	40	197	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
992	"Nihil ut ea nihil recusandae Fugiat veritatis dolores nisi veritatis delectus ullamco perferendis do voluptatem Nulla non"	\N	App\\Models\\IdeeProjet	40	190	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
993	["Soluta minim cumque officia non aut ut laborum Nam corrupti quis labore voluptatem Id quia sed"]	\N	App\\Models\\IdeeProjet	40	191	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
994	["Dolor in omnis enim qui sint esse a aliquam deleniti aut magna"]	\N	App\\Models\\IdeeProjet	40	192	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
995	"Temporibus corporis dolor dolores veritatis ea error nisi vel esse occaecat impedit ipsam ut fugiat dolorem sint qui reprehenderit"	\N	App\\Models\\IdeeProjet	40	193	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
996	"Sint odit nobis sunt inventore a amet anim repudiandae dolorem laboris quos et a"	\N	App\\Models\\IdeeProjet	40	194	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
997	"Hic quibusdam qui eaque sapiente sit pariatur Harum non sunt aut perferendis aut commodo quam sint sit dolore cillum"	\N	App\\Models\\IdeeProjet	40	195	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
998	"Dolorum corporis accusamus error cum itaque repellendus Eos atque"	\N	App\\Models\\IdeeProjet	40	204	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
999	"Obcaecati ipsum ad nihil ut commodi dolore in deserunt"	\N	App\\Models\\IdeeProjet	40	201	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
1000	"Velit assumenda accusantium eligendi blanditiis id quam"	\N	App\\Models\\IdeeProjet	40	207	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
1001	"Voluptatem non voluptatem fugit ullam reprehenderit numquam commodi debitis facere et asperiores esse et"	\N	App\\Models\\IdeeProjet	40	208	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
1002	"Dolorem in proident cumque hic sed corrupti obcaecati incidunt magnam vero velit ex sint nostrum"	\N	App\\Models\\IdeeProjet	40	202	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
1003	"Suscipit dolores aliquid provident minim dignissimos voluptatem vitae magni itaque"	\N	App\\Models\\IdeeProjet	40	203	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
1004	"Voluptates voluptate corrupti quis voluptas qui atque natus non provident eiusmod consequatur Dolore eius veniam molestiae"	\N	App\\Models\\IdeeProjet	40	198	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
1005	"Facilis architecto ad consectetur quod tempor corrupti laboriosam optio"	\N	App\\Models\\IdeeProjet	40	200	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
1006	"Qui ducimus commodi nisi maxime consequatur veniam nihil"	\N	App\\Models\\IdeeProjet	40	199	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
1007	"Iste officia tempore ut beatae et repudiandae aliqua Eu distinctio Ut natus laborum ea deserunt duis"	\N	App\\Models\\IdeeProjet	40	205	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
1008	"Sit sed debitis sapiente aut dolore aliquid culpa ab cum maxime quia in quos placeat deleniti"	\N	App\\Models\\IdeeProjet	40	206	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
1009	"Nihil lorem porro doloribus et neque labore eum minima atque enim cupiditate sit temporibus optio fugiat earum"	\N	App\\Models\\IdeeProjet	41	161	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1010	"Fugit unde aut iste dolor voluptas exercitation q"	\N	App\\Models\\IdeeProjet	41	162	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1011	1	\N	App\\Models\\IdeeProjet	41	163	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1012	[68]	\N	App\\Models\\IdeeProjet	41	165	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1013	93	\N	App\\Models\\IdeeProjet	41	169	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1014	11	\N	App\\Models\\IdeeProjet	41	170	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1015	{"devise": "FCFA", "montant": 4}	\N	App\\Models\\IdeeProjet	41	166	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1016	26	\N	App\\Models\\IdeeProjet	41	168	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1017	\N	\N	App\\Models\\IdeeProjet	41	171	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1018	\N	\N	App\\Models\\IdeeProjet	41	172	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1019	\N	\N	App\\Models\\IdeeProjet	41	173	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1020	[]	\N	App\\Models\\IdeeProjet	41	174	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1021	[]	\N	App\\Models\\IdeeProjet	41	175	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1022	[]	\N	App\\Models\\IdeeProjet	41	176	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1023	[]	\N	App\\Models\\IdeeProjet	41	177	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1024	[]	\N	App\\Models\\IdeeProjet	41	178	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1025	[]	\N	App\\Models\\IdeeProjet	41	179	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1026	[]	\N	App\\Models\\IdeeProjet	41	180	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1027	[]	\N	App\\Models\\IdeeProjet	41	181	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1028	[]	\N	App\\Models\\IdeeProjet	41	182	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1029	[]	\N	App\\Models\\IdeeProjet	41	212	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1030	[]	\N	App\\Models\\IdeeProjet	41	183	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1031	[]	\N	App\\Models\\IdeeProjet	41	184	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1032	\N	\N	App\\Models\\IdeeProjet	41	185	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1033	\N	\N	App\\Models\\IdeeProjet	41	186	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1034	[]	\N	App\\Models\\IdeeProjet	41	187	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1035	\N	\N	App\\Models\\IdeeProjet	41	188	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1036	\N	\N	App\\Models\\IdeeProjet	41	213	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1037	[]	\N	App\\Models\\IdeeProjet	41	189	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1038	\N	\N	App\\Models\\IdeeProjet	41	190	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1039	[]	\N	App\\Models\\IdeeProjet	41	191	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1040	[]	\N	App\\Models\\IdeeProjet	41	192	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1041	\N	\N	App\\Models\\IdeeProjet	41	193	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1042	\N	\N	App\\Models\\IdeeProjet	41	194	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1043	\N	\N	App\\Models\\IdeeProjet	41	195	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1044	\N	\N	App\\Models\\IdeeProjet	41	196	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1045	\N	\N	App\\Models\\IdeeProjet	41	197	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1046	\N	\N	App\\Models\\IdeeProjet	41	198	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1047	\N	\N	App\\Models\\IdeeProjet	41	199	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1048	\N	\N	App\\Models\\IdeeProjet	41	200	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1049	\N	\N	App\\Models\\IdeeProjet	41	201	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1050	\N	\N	App\\Models\\IdeeProjet	41	202	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1051	\N	\N	App\\Models\\IdeeProjet	41	204	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1052	\N	\N	App\\Models\\IdeeProjet	41	203	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1053	\N	\N	App\\Models\\IdeeProjet	41	205	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1054	\N	\N	App\\Models\\IdeeProjet	41	208	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1055	\N	\N	App\\Models\\IdeeProjet	41	207	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1056	\N	\N	App\\Models\\IdeeProjet	41	206	2025-07-25 20:22:04	2025-07-25 20:22:04	\N
1057	"Modi labs sitfvel aliquid occaecat voluptatem aspernatur quam qui sunt ab in sed reprehenderit voluptatem"	\N	App\\Models\\IdeeProjet	42	161	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1058	"Omnis sit odit opt doore ofs dorumjhgf est"	\N	App\\Models\\IdeeProjet	42	162	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1059	1	\N	App\\Models\\IdeeProjet	42	163	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1060	[87]	\N	App\\Models\\IdeeProjet	42	165	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1061	10	\N	App\\Models\\IdeeProjet	42	169	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1062	78	\N	App\\Models\\IdeeProjet	42	170	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1063	{"devise": "FCFA", "montant": 68}	\N	App\\Models\\IdeeProjet	42	166	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1064	3	\N	App\\Models\\IdeeProjet	42	168	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1065	[2]	\N	App\\Models\\IdeeProjet	42	176	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1066	[1]	\N	App\\Models\\IdeeProjet	42	177	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1067	19	\N	App\\Models\\IdeeProjet	42	171	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1068	20	\N	App\\Models\\IdeeProjet	42	172	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1069	27	\N	App\\Models\\IdeeProjet	42	173	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1070	[3]	\N	App\\Models\\IdeeProjet	42	174	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1071	[1]	\N	App\\Models\\IdeeProjet	42	175	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1072	[4]	\N	App\\Models\\IdeeProjet	42	182	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1073	[3]	\N	App\\Models\\IdeeProjet	42	183	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1074	[10]	\N	App\\Models\\IdeeProjet	42	184	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1075	[9]	\N	App\\Models\\IdeeProjet	42	178	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1076	[7]	\N	App\\Models\\IdeeProjet	42	212	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1077	[8]	\N	App\\Models\\IdeeProjet	42	180	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1078	[14]	\N	App\\Models\\IdeeProjet	42	181	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1079	[5]	\N	App\\Models\\IdeeProjet	42	179	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1080	["Ipsum voluptas sunt consequat Proident harum explicabo Deserunt accusantium dolor aut fuga Mollit iusto quia distinctio Incididunt unde vel"]	\N	App\\Models\\IdeeProjet	42	189	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1081	"Sint do delectus magnam quisquam labore"	\N	App\\Models\\IdeeProjet	42	213	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1082	[10]	\N	App\\Models\\IdeeProjet	42	187	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1083	"Elit rerum veniam quas quis excepteur sed dolore nihil ullam qui"	\N	App\\Models\\IdeeProjet	42	188	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1084	4	\N	App\\Models\\IdeeProjet	42	185	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1085	6	\N	App\\Models\\IdeeProjet	42	186	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1086	"Aut dolorem do non minus labore illum architecto in dignissimos ea quia qui"	\N	App\\Models\\IdeeProjet	42	196	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1087	"Fuga Voluptas dolor ut et pariatur Libero accusantium nulla magni duis qui nisi laudantium commodo iure incidunt"	\N	App\\Models\\IdeeProjet	42	197	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1240	"Velit assumenda accusantium eligendi blanditiis id quam"	\N	App\\Models\\IdeeProjet	45	207	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1088	"Nihil ut ea nihil recusandae Fugiat veritatis dolores nisi veritatis delectus ullamco perferendis do voluptatem Nulla non"	\N	App\\Models\\IdeeProjet	42	190	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1089	["Soluta minim cumque officia non aut ut laborum Nam corrupti quis labore voluptatem Id quia sed"]	\N	App\\Models\\IdeeProjet	42	191	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1090	["Dolor in omnis enim qui sint esse a aliquam deleniti aut magna"]	\N	App\\Models\\IdeeProjet	42	192	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1091	"Temporibus corporis dolor dolores veritatis ea error nisi vel esse occaecat impedit ipsam ut fugiat dolorem sint qui reprehenderit"	\N	App\\Models\\IdeeProjet	42	193	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1092	"Sint odit nobis sunt inventore a amet anim repudiandae dolorem laboris quos et a"	\N	App\\Models\\IdeeProjet	42	194	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1093	"Hic quibusdam qui eaque sapiente sit pariatur Harum non sunt aut perferendis aut commodo quam sint sit dolore cillum"	\N	App\\Models\\IdeeProjet	42	195	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1094	"Dolorum corporis accusamus error cum itaque repellendus Eos atque"	\N	App\\Models\\IdeeProjet	42	204	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1095	"Obcaecati ipsum ad nihil ut commodi dolore in deserunt"	\N	App\\Models\\IdeeProjet	42	201	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1096	"Velit assumenda accusantium eligendi blanditiis id quam"	\N	App\\Models\\IdeeProjet	42	207	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1097	"Voluptatem non voluptatem fugit ullam reprehenderit numquam commodi debitis facere et asperiores esse et"	\N	App\\Models\\IdeeProjet	42	208	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1098	"Dolorem in proident cumque hic sed corrupti obcaecati incidunt magnam vero velit ex sint nostrum"	\N	App\\Models\\IdeeProjet	42	202	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1099	"Suscipit dolores aliquid provident minim dignissimos voluptatem vitae magni itaque"	\N	App\\Models\\IdeeProjet	42	203	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1100	"Voluptates voluptate corrupti quis voluptas qui atque natus non provident eiusmod consequatur Dolore eius veniam molestiae"	\N	App\\Models\\IdeeProjet	42	198	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1101	"Facilis architecto ad consectetur quod tempor corrupti laboriosam optio"	\N	App\\Models\\IdeeProjet	42	200	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1102	"Qui ducimus commodi nisi maxime consequatur veniam nihil"	\N	App\\Models\\IdeeProjet	42	199	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1103	"Iste officia tempore ut beatae et repudiandae aliqua Eu distinctio Ut natus laborum ea deserunt duis"	\N	App\\Models\\IdeeProjet	42	205	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1104	"Sit sed debitis sapiente aut dolore aliquid culpa ab cum maxime quia in quos placeat deleniti"	\N	App\\Models\\IdeeProjet	42	206	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
1105	"Modi labs sitfvel aliquidcaecat voluptatem aspernatur quam qui sunt ab in sed reprehenderit voluptatem"	\N	App\\Models\\IdeeProjet	43	161	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1106	"Omnis sit odit opt doore ofs dumjhgf est"	\N	App\\Models\\IdeeProjet	43	162	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1107	1	\N	App\\Models\\IdeeProjet	43	163	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1108	[87]	\N	App\\Models\\IdeeProjet	43	165	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1109	10	\N	App\\Models\\IdeeProjet	43	169	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1110	78	\N	App\\Models\\IdeeProjet	43	170	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1111	{"devise": "FCFA", "montant": 68}	\N	App\\Models\\IdeeProjet	43	166	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1112	3	\N	App\\Models\\IdeeProjet	43	168	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1113	[2]	\N	App\\Models\\IdeeProjet	43	176	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1114	[1]	\N	App\\Models\\IdeeProjet	43	177	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1115	19	\N	App\\Models\\IdeeProjet	43	171	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1116	20	\N	App\\Models\\IdeeProjet	43	172	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1117	27	\N	App\\Models\\IdeeProjet	43	173	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1118	[3]	\N	App\\Models\\IdeeProjet	43	174	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1119	[1]	\N	App\\Models\\IdeeProjet	43	175	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1120	[4]	\N	App\\Models\\IdeeProjet	43	182	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1121	[3]	\N	App\\Models\\IdeeProjet	43	183	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1122	[10]	\N	App\\Models\\IdeeProjet	43	184	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1123	[9]	\N	App\\Models\\IdeeProjet	43	178	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1124	[7]	\N	App\\Models\\IdeeProjet	43	212	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1125	[8]	\N	App\\Models\\IdeeProjet	43	180	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1126	[14]	\N	App\\Models\\IdeeProjet	43	181	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1127	[5]	\N	App\\Models\\IdeeProjet	43	179	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1128	["Ipsum voluptas sunt consequat Proident harum explicabo Deserunt accusantium dolor aut fuga Mollit iusto quia distinctio Incididunt unde vel"]	\N	App\\Models\\IdeeProjet	43	189	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1129	"Sint do delectus magnam quisquam labore"	\N	App\\Models\\IdeeProjet	43	213	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1130	[10]	\N	App\\Models\\IdeeProjet	43	187	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1131	"Elit rerum veniam quas quis excepteur sed dolore nihil ullam qui"	\N	App\\Models\\IdeeProjet	43	188	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1132	4	\N	App\\Models\\IdeeProjet	43	185	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1133	6	\N	App\\Models\\IdeeProjet	43	186	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1134	"Aut dolorem do non minus labore illum architecto in dignissimos ea quia qui"	\N	App\\Models\\IdeeProjet	43	196	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1135	"Fuga Voluptas dolor ut et pariatur Libero accusantium nulla magni duis qui nisi laudantium commodo iure incidunt"	\N	App\\Models\\IdeeProjet	43	197	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1136	"Nihil ut ea nihil recusandae Fugiat veritatis dolores nisi veritatis delectus ullamco perferendis do voluptatem Nulla non"	\N	App\\Models\\IdeeProjet	43	190	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1137	["Soluta minim cumque officia non aut ut laborum Nam corrupti quis labore voluptatem Id quia sed"]	\N	App\\Models\\IdeeProjet	43	191	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1138	["Dolor in omnis enim qui sint esse a aliquam deleniti aut magna"]	\N	App\\Models\\IdeeProjet	43	192	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1139	"Temporibus corporis dolor dolores veritatis ea error nisi vel esse occaecat impedit ipsam ut fugiat dolorem sint qui reprehenderit"	\N	App\\Models\\IdeeProjet	43	193	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1140	"Sint odit nobis sunt inventore a amet anim repudiandae dolorem laboris quos et a"	\N	App\\Models\\IdeeProjet	43	194	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1141	"Hic quibusdam qui eaque sapiente sit pariatur Harum non sunt aut perferendis aut commodo quam sint sit dolore cillum"	\N	App\\Models\\IdeeProjet	43	195	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1142	"Dolorum corporis accusamus error cum itaque repellendus Eos atque"	\N	App\\Models\\IdeeProjet	43	204	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1143	"Obcaecati ipsum ad nihil ut commodi dolore in deserunt"	\N	App\\Models\\IdeeProjet	43	201	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1144	"Velit assumenda accusantium eligendi blanditiis id quam"	\N	App\\Models\\IdeeProjet	43	207	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1145	"Voluptatem non voluptatem fugit ullam reprehenderit numquam commodi debitis facere et asperiores esse et"	\N	App\\Models\\IdeeProjet	43	208	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1146	"Dolorem in proident cumque hic sed corrupti obcaecati incidunt magnam vero velit ex sint nostrum"	\N	App\\Models\\IdeeProjet	43	202	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1147	"Suscipit dolores aliquid provident minim dignissimos voluptatem vitae magni itaque"	\N	App\\Models\\IdeeProjet	43	203	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1148	"Voluptates voluptate corrupti quis voluptas qui atque natus non provident eiusmod consequatur Dolore eius veniam molestiae"	\N	App\\Models\\IdeeProjet	43	198	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1149	"Facilis architecto ad consectetur quod tempor corrupti laboriosam optio"	\N	App\\Models\\IdeeProjet	43	200	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1150	"Qui ducimus commodi nisi maxime consequatur veniam nihil"	\N	App\\Models\\IdeeProjet	43	199	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1151	"Iste officia tempore ut beatae et repudiandae aliqua Eu distinctio Ut natus laborum ea deserunt duis"	\N	App\\Models\\IdeeProjet	43	205	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1152	"Sit sed debitis sapiente aut dolore aliquid culpa ab cum maxime quia in quos placeat deleniti"	\N	App\\Models\\IdeeProjet	43	206	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
1153	"Modi labs sitfvel aliqffuidcaecat voluptatem aspernatur quam qui sunt ab in sed reprehenderit voluptatem"	\N	App\\Models\\IdeeProjet	44	161	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1154	"Omnis sit odit opt doore fofs dumjhgf est"	\N	App\\Models\\IdeeProjet	44	162	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1155	1	\N	App\\Models\\IdeeProjet	44	163	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1156	[87]	\N	App\\Models\\IdeeProjet	44	165	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1157	10	\N	App\\Models\\IdeeProjet	44	169	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1158	78	\N	App\\Models\\IdeeProjet	44	170	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1159	{"devise": "FCFA", "montant": 68}	\N	App\\Models\\IdeeProjet	44	166	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1160	3	\N	App\\Models\\IdeeProjet	44	168	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1161	[2]	\N	App\\Models\\IdeeProjet	44	176	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1162	[1]	\N	App\\Models\\IdeeProjet	44	177	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1163	19	\N	App\\Models\\IdeeProjet	44	171	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1164	20	\N	App\\Models\\IdeeProjet	44	172	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1165	27	\N	App\\Models\\IdeeProjet	44	173	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1166	[3]	\N	App\\Models\\IdeeProjet	44	174	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1167	[1]	\N	App\\Models\\IdeeProjet	44	175	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1168	[4]	\N	App\\Models\\IdeeProjet	44	182	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1169	[3]	\N	App\\Models\\IdeeProjet	44	183	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1170	[10]	\N	App\\Models\\IdeeProjet	44	184	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1171	[9]	\N	App\\Models\\IdeeProjet	44	178	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1172	[7]	\N	App\\Models\\IdeeProjet	44	212	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1173	[8]	\N	App\\Models\\IdeeProjet	44	180	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1174	[14]	\N	App\\Models\\IdeeProjet	44	181	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1175	[5]	\N	App\\Models\\IdeeProjet	44	179	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1176	["Ipsum voluptas sunt consequat Proident harum explicabo Deserunt accusantium dolor aut fuga Mollit iusto quia distinctio Incididunt unde vel"]	\N	App\\Models\\IdeeProjet	44	189	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1177	"Sint do delectus magnam quisquam labore"	\N	App\\Models\\IdeeProjet	44	213	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1178	[10]	\N	App\\Models\\IdeeProjet	44	187	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1179	"Elit rerum veniam quas quis excepteur sed dolore nihil ullam qui"	\N	App\\Models\\IdeeProjet	44	188	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1180	4	\N	App\\Models\\IdeeProjet	44	185	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1181	6	\N	App\\Models\\IdeeProjet	44	186	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1182	"Aut dolorem do non minus labore illum architecto in dignissimos ea quia qui"	\N	App\\Models\\IdeeProjet	44	196	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1183	"Fuga Voluptas dolor ut et pariatur Libero accusantium nulla magni duis qui nisi laudantium commodo iure incidunt"	\N	App\\Models\\IdeeProjet	44	197	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1184	"Nihil ut ea nihil recusandae Fugiat veritatis dolores nisi veritatis delectus ullamco perferendis do voluptatem Nulla non"	\N	App\\Models\\IdeeProjet	44	190	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1185	["Soluta minim cumque officia non aut ut laborum Nam corrupti quis labore voluptatem Id quia sed"]	\N	App\\Models\\IdeeProjet	44	191	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1186	["Dolor in omnis enim qui sint esse a aliquam deleniti aut magna"]	\N	App\\Models\\IdeeProjet	44	192	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1187	"Temporibus corporis dolor dolores veritatis ea error nisi vel esse occaecat impedit ipsam ut fugiat dolorem sint qui reprehenderit"	\N	App\\Models\\IdeeProjet	44	193	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1188	"Sint odit nobis sunt inventore a amet anim repudiandae dolorem laboris quos et a"	\N	App\\Models\\IdeeProjet	44	194	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1307	{"devise": "FCFA", "montant": 39}	\N	App\\Models\\IdeeProjet	60	166	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1189	"Hic quibusdam qui eaque sapiente sit pariatur Harum non sunt aut perferendis aut commodo quam sint sit dolore cillum"	\N	App\\Models\\IdeeProjet	44	195	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1190	"Dolorum corporis accusamus error cum itaque repellendus Eos atque"	\N	App\\Models\\IdeeProjet	44	204	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1191	"Obcaecati ipsum ad nihil ut commodi dolore in deserunt"	\N	App\\Models\\IdeeProjet	44	201	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1192	"Velit assumenda accusantium eligendi blanditiis id quam"	\N	App\\Models\\IdeeProjet	44	207	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1193	"Voluptatem non voluptatem fugit ullam reprehenderit numquam commodi debitis facere et asperiores esse et"	\N	App\\Models\\IdeeProjet	44	208	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1194	"Dolorem in proident cumque hic sed corrupti obcaecati incidunt magnam vero velit ex sint nostrum"	\N	App\\Models\\IdeeProjet	44	202	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1195	"Suscipit dolores aliquid provident minim dignissimos voluptatem vitae magni itaque"	\N	App\\Models\\IdeeProjet	44	203	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1196	"Voluptates voluptate corrupti quis voluptas qui atque natus non provident eiusmod consequatur Dolore eius veniam molestiae"	\N	App\\Models\\IdeeProjet	44	198	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1197	"Facilis architecto ad consectetur quod tempor corrupti laboriosam optio"	\N	App\\Models\\IdeeProjet	44	200	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1198	"Qui ducimus commodi nisi maxime consequatur veniam nihil"	\N	App\\Models\\IdeeProjet	44	199	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1199	"Iste officia tempore ut beatae et repudiandae aliqua Eu distinctio Ut natus laborum ea deserunt duis"	\N	App\\Models\\IdeeProjet	44	205	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1200	"Sit sed debitis sapiente aut dolore aliquid culpa ab cum maxime quia in quos placeat deleniti"	\N	App\\Models\\IdeeProjet	44	206	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
1201	"Modi labs sitfvefffl aliqffuidcaecat voluptatem aspernatur quam qui sunt ab in sed reprehenderit voluptatem"	\N	App\\Models\\IdeeProjet	45	161	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1202	"Omnis sit odit opt fffff fofs dumjhgf est"	\N	App\\Models\\IdeeProjet	45	162	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1203	1	\N	App\\Models\\IdeeProjet	45	163	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1204	[87]	\N	App\\Models\\IdeeProjet	45	165	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1205	10	\N	App\\Models\\IdeeProjet	45	169	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1206	78	\N	App\\Models\\IdeeProjet	45	170	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1207	{"devise": "FCFA", "montant": 68}	\N	App\\Models\\IdeeProjet	45	166	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1208	3	\N	App\\Models\\IdeeProjet	45	168	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1209	[2]	\N	App\\Models\\IdeeProjet	45	176	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1210	[1]	\N	App\\Models\\IdeeProjet	45	177	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1211	19	\N	App\\Models\\IdeeProjet	45	171	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1212	20	\N	App\\Models\\IdeeProjet	45	172	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1213	27	\N	App\\Models\\IdeeProjet	45	173	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1214	[3]	\N	App\\Models\\IdeeProjet	45	174	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1215	[1]	\N	App\\Models\\IdeeProjet	45	175	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1216	[4]	\N	App\\Models\\IdeeProjet	45	182	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1217	[3]	\N	App\\Models\\IdeeProjet	45	183	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1218	[10]	\N	App\\Models\\IdeeProjet	45	184	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1219	[9]	\N	App\\Models\\IdeeProjet	45	178	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1220	[7]	\N	App\\Models\\IdeeProjet	45	212	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1221	[8]	\N	App\\Models\\IdeeProjet	45	180	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1222	[14]	\N	App\\Models\\IdeeProjet	45	181	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1223	[5]	\N	App\\Models\\IdeeProjet	45	179	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1224	["Ipsum voluptas sunt consequat Proident harum explicabo Deserunt accusantium dolor aut fuga Mollit iusto quia distinctio Incididunt unde vel"]	\N	App\\Models\\IdeeProjet	45	189	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1225	"Sint do delectus magnam quisquam labore"	\N	App\\Models\\IdeeProjet	45	213	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1226	[10]	\N	App\\Models\\IdeeProjet	45	187	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1227	"Elit rerum veniam quas quis excepteur sed dolore nihil ullam qui"	\N	App\\Models\\IdeeProjet	45	188	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1228	4	\N	App\\Models\\IdeeProjet	45	185	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1229	6	\N	App\\Models\\IdeeProjet	45	186	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1230	"Aut dolorem do non minus labore illum architecto in dignissimos ea quia qui"	\N	App\\Models\\IdeeProjet	45	196	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1231	"Fuga Voluptas dolor ut et pariatur Libero accusantium nulla magni duis qui nisi laudantium commodo iure incidunt"	\N	App\\Models\\IdeeProjet	45	197	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1232	"Nihil ut ea nihil recusandae Fugiat veritatis dolores nisi veritatis delectus ullamco perferendis do voluptatem Nulla non"	\N	App\\Models\\IdeeProjet	45	190	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1233	["Soluta minim cumque officia non aut ut laborum Nam corrupti quis labore voluptatem Id quia sed"]	\N	App\\Models\\IdeeProjet	45	191	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1234	["Dolor in omnis enim qui sint esse a aliquam deleniti aut magna"]	\N	App\\Models\\IdeeProjet	45	192	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1235	"Temporibus corporis dolor dolores veritatis ea error nisi vel esse occaecat impedit ipsam ut fugiat dolorem sint qui reprehenderit"	\N	App\\Models\\IdeeProjet	45	193	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1236	"Sint odit nobis sunt inventore a amet anim repudiandae dolorem laboris quos et a"	\N	App\\Models\\IdeeProjet	45	194	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1237	"Hic quibusdam qui eaque sapiente sit pariatur Harum non sunt aut perferendis aut commodo quam sint sit dolore cillum"	\N	App\\Models\\IdeeProjet	45	195	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1238	"Dolorum corporis accusamus error cum itaque repellendus Eos atque"	\N	App\\Models\\IdeeProjet	45	204	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1239	"Obcaecati ipsum ad nihil ut commodi dolore in deserunt"	\N	App\\Models\\IdeeProjet	45	201	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1241	"Voluptatem non voluptatem fugit ullam reprehenderit numquam commodi debitis facere et asperiores esse et"	\N	App\\Models\\IdeeProjet	45	208	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1242	"Dolorem in proident cumque hic sed corrupti obcaecati incidunt magnam vero velit ex sint nostrum"	\N	App\\Models\\IdeeProjet	45	202	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1243	"Suscipit dolores aliquid provident minim dignissimos voluptatem vitae magni itaque"	\N	App\\Models\\IdeeProjet	45	203	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1244	"Voluptates voluptate corrupti quis voluptas qui atque natus non provident eiusmod consequatur Dolore eius veniam molestiae"	\N	App\\Models\\IdeeProjet	45	198	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1245	"Facilis architecto ad consectetur quod tempor corrupti laboriosam optio"	\N	App\\Models\\IdeeProjet	45	200	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1246	"Qui ducimus commodi nisi maxime consequatur veniam nihil"	\N	App\\Models\\IdeeProjet	45	199	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1247	"Iste officia tempore ut beatae et repudiandae aliqua Eu distinctio Ut natus laborum ea deserunt duis"	\N	App\\Models\\IdeeProjet	45	205	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1248	"Sit sed debitis sapiente aut dolore aliquid culpa ab cum maxime quia in quos placeat deleniti"	\N	App\\Models\\IdeeProjet	45	206	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
1249	"onsequaturdre ftryryhg rerum"	\N	App\\Models\\IdeeProjet	46	161	2025-07-27 19:16:49	2025-07-27 19:16:49	\N
1250	"onsequaturdre ftryryhg rerum"	\N	App\\Models\\IdeeProjet	53	161	2025-07-28 10:13:06	2025-07-28 10:13:06	\N
1251	"onsequaturdre sftryryhg rerum"	\N	App\\Models\\IdeeProjet	57	161	2025-07-28 11:15:49	2025-07-28 11:15:49	\N
1252	"Beatae Nam aliquip ullamco aliqua Labore"	\N	App\\Models\\IdeeProjet	58	161	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1253	"Voluptas sint eu quasi deserunt proident est su"	\N	App\\Models\\IdeeProjet	58	162	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1254	\N	\N	App\\Models\\IdeeProjet	58	163	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1255	[11]	\N	App\\Models\\IdeeProjet	58	165	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1256	62	\N	App\\Models\\IdeeProjet	58	169	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1257	7	\N	App\\Models\\IdeeProjet	58	170	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1258	{"devise": "FCFA", "montant": 43}	\N	App\\Models\\IdeeProjet	58	166	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1259	87	\N	App\\Models\\IdeeProjet	58	168	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1260	\N	\N	App\\Models\\IdeeProjet	58	171	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1261	\N	\N	App\\Models\\IdeeProjet	58	172	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1262	\N	\N	App\\Models\\IdeeProjet	58	173	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1263	[]	\N	App\\Models\\IdeeProjet	58	174	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1264	[]	\N	App\\Models\\IdeeProjet	58	175	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1265	[]	\N	App\\Models\\IdeeProjet	58	176	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1266	[]	\N	App\\Models\\IdeeProjet	58	177	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1267	[]	\N	App\\Models\\IdeeProjet	58	178	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1268	[]	\N	App\\Models\\IdeeProjet	58	179	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1269	[]	\N	App\\Models\\IdeeProjet	58	180	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1270	[]	\N	App\\Models\\IdeeProjet	58	181	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1271	[]	\N	App\\Models\\IdeeProjet	58	182	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1272	[]	\N	App\\Models\\IdeeProjet	58	212	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1273	[]	\N	App\\Models\\IdeeProjet	58	183	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1274	[]	\N	App\\Models\\IdeeProjet	58	184	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1275	\N	\N	App\\Models\\IdeeProjet	58	185	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1276	\N	\N	App\\Models\\IdeeProjet	58	186	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1277	[]	\N	App\\Models\\IdeeProjet	58	187	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1278	\N	\N	App\\Models\\IdeeProjet	58	188	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1279	\N	\N	App\\Models\\IdeeProjet	58	213	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1280	[]	\N	App\\Models\\IdeeProjet	58	189	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1281	\N	\N	App\\Models\\IdeeProjet	58	190	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1282	[]	\N	App\\Models\\IdeeProjet	58	191	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1283	[]	\N	App\\Models\\IdeeProjet	58	192	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1284	\N	\N	App\\Models\\IdeeProjet	58	193	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1285	\N	\N	App\\Models\\IdeeProjet	58	194	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1286	\N	\N	App\\Models\\IdeeProjet	58	195	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1287	\N	\N	App\\Models\\IdeeProjet	58	196	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1288	\N	\N	App\\Models\\IdeeProjet	58	197	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1289	\N	\N	App\\Models\\IdeeProjet	58	198	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1290	\N	\N	App\\Models\\IdeeProjet	58	199	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1291	\N	\N	App\\Models\\IdeeProjet	58	200	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1292	\N	\N	App\\Models\\IdeeProjet	58	201	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1293	\N	\N	App\\Models\\IdeeProjet	58	202	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1294	\N	\N	App\\Models\\IdeeProjet	58	204	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1295	\N	\N	App\\Models\\IdeeProjet	58	203	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1296	\N	\N	App\\Models\\IdeeProjet	58	205	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1297	\N	\N	App\\Models\\IdeeProjet	58	208	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1298	\N	\N	App\\Models\\IdeeProjet	58	207	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1299	\N	\N	App\\Models\\IdeeProjet	58	206	2025-07-28 11:19:41	2025-07-28 11:19:41	\N
1300	"onsequaturdre sftryryhg rerum"	\N	App\\Models\\IdeeProjet	59	161	2025-07-28 11:20:50	2025-07-28 11:20:50	\N
1301	"Omnis eius voluptates esse corrupti beatae"	\N	App\\Models\\IdeeProjet	60	161	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1302	"Quis blanditiis necessitatibus quasi voluptas laud"	\N	App\\Models\\IdeeProjet	60	162	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1303	\N	\N	App\\Models\\IdeeProjet	60	163	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1304	[75]	\N	App\\Models\\IdeeProjet	60	165	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1305	57	\N	App\\Models\\IdeeProjet	60	169	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1306	52	\N	App\\Models\\IdeeProjet	60	170	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1308	84	\N	App\\Models\\IdeeProjet	60	168	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1309	\N	\N	App\\Models\\IdeeProjet	60	171	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1310	\N	\N	App\\Models\\IdeeProjet	60	172	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1311	\N	\N	App\\Models\\IdeeProjet	60	173	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1312	[]	\N	App\\Models\\IdeeProjet	60	174	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1313	[]	\N	App\\Models\\IdeeProjet	60	175	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1314	[]	\N	App\\Models\\IdeeProjet	60	176	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1315	[]	\N	App\\Models\\IdeeProjet	60	177	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1316	[]	\N	App\\Models\\IdeeProjet	60	178	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1317	[]	\N	App\\Models\\IdeeProjet	60	179	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1318	[]	\N	App\\Models\\IdeeProjet	60	180	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1319	[]	\N	App\\Models\\IdeeProjet	60	181	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1320	[]	\N	App\\Models\\IdeeProjet	60	182	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1321	[]	\N	App\\Models\\IdeeProjet	60	212	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1322	[]	\N	App\\Models\\IdeeProjet	60	183	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1323	[]	\N	App\\Models\\IdeeProjet	60	184	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1324	\N	\N	App\\Models\\IdeeProjet	60	185	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1325	\N	\N	App\\Models\\IdeeProjet	60	186	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1326	[]	\N	App\\Models\\IdeeProjet	60	187	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1327	\N	\N	App\\Models\\IdeeProjet	60	188	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1328	\N	\N	App\\Models\\IdeeProjet	60	213	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1329	[]	\N	App\\Models\\IdeeProjet	60	189	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1330	\N	\N	App\\Models\\IdeeProjet	60	190	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1331	[]	\N	App\\Models\\IdeeProjet	60	191	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1332	[]	\N	App\\Models\\IdeeProjet	60	192	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1333	\N	\N	App\\Models\\IdeeProjet	60	193	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1334	\N	\N	App\\Models\\IdeeProjet	60	194	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1335	\N	\N	App\\Models\\IdeeProjet	60	195	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1336	\N	\N	App\\Models\\IdeeProjet	60	196	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1337	\N	\N	App\\Models\\IdeeProjet	60	197	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1338	\N	\N	App\\Models\\IdeeProjet	60	198	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1339	\N	\N	App\\Models\\IdeeProjet	60	199	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1340	\N	\N	App\\Models\\IdeeProjet	60	200	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1341	\N	\N	App\\Models\\IdeeProjet	60	201	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1342	\N	\N	App\\Models\\IdeeProjet	60	202	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1343	\N	\N	App\\Models\\IdeeProjet	60	204	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1344	\N	\N	App\\Models\\IdeeProjet	60	203	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1345	\N	\N	App\\Models\\IdeeProjet	60	205	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1346	\N	\N	App\\Models\\IdeeProjet	60	208	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1347	\N	\N	App\\Models\\IdeeProjet	60	207	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1348	\N	\N	App\\Models\\IdeeProjet	60	206	2025-07-28 11:21:16	2025-07-28 11:21:16	\N
1349	"Explicabo Nostrum rerum animi veritatis assumenda laborum officia facilis voluptate id unde velit a facilis adipisicing dolores"	\N	App\\Models\\IdeeProjet	61	161	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1350	"Non omnis sed autem qui cupidatat aliquid odio ess"	\N	App\\Models\\IdeeProjet	61	162	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1351	\N	\N	App\\Models\\IdeeProjet	61	163	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1352	[49]	\N	App\\Models\\IdeeProjet	61	165	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1353	70	\N	App\\Models\\IdeeProjet	61	169	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1354	26	\N	App\\Models\\IdeeProjet	61	170	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1355	{"devise": "FCFA", "montant": 63}	\N	App\\Models\\IdeeProjet	61	166	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1356	79	\N	App\\Models\\IdeeProjet	61	168	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1357	\N	\N	App\\Models\\IdeeProjet	61	171	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1358	\N	\N	App\\Models\\IdeeProjet	61	172	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1359	\N	\N	App\\Models\\IdeeProjet	61	173	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1360	[]	\N	App\\Models\\IdeeProjet	61	174	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1361	[]	\N	App\\Models\\IdeeProjet	61	175	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1362	[]	\N	App\\Models\\IdeeProjet	61	176	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1363	[]	\N	App\\Models\\IdeeProjet	61	177	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1364	[]	\N	App\\Models\\IdeeProjet	61	178	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1365	[]	\N	App\\Models\\IdeeProjet	61	179	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1366	[]	\N	App\\Models\\IdeeProjet	61	180	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1367	[]	\N	App\\Models\\IdeeProjet	61	181	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1368	[]	\N	App\\Models\\IdeeProjet	61	182	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1369	[]	\N	App\\Models\\IdeeProjet	61	212	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1370	[]	\N	App\\Models\\IdeeProjet	61	183	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1371	[]	\N	App\\Models\\IdeeProjet	61	184	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1372	\N	\N	App\\Models\\IdeeProjet	61	185	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1373	\N	\N	App\\Models\\IdeeProjet	61	186	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1374	[]	\N	App\\Models\\IdeeProjet	61	187	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1375	\N	\N	App\\Models\\IdeeProjet	61	188	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1376	\N	\N	App\\Models\\IdeeProjet	61	213	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1377	[]	\N	App\\Models\\IdeeProjet	61	189	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1378	\N	\N	App\\Models\\IdeeProjet	61	190	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1379	[]	\N	App\\Models\\IdeeProjet	61	191	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1380	[]	\N	App\\Models\\IdeeProjet	61	192	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1381	\N	\N	App\\Models\\IdeeProjet	61	193	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1382	\N	\N	App\\Models\\IdeeProjet	61	194	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1385	\N	\N	App\\Models\\IdeeProjet	61	197	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1386	\N	\N	App\\Models\\IdeeProjet	61	198	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1387	\N	\N	App\\Models\\IdeeProjet	61	199	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1388	\N	\N	App\\Models\\IdeeProjet	61	200	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1389	\N	\N	App\\Models\\IdeeProjet	61	201	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1390	\N	\N	App\\Models\\IdeeProjet	61	202	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1391	\N	\N	App\\Models\\IdeeProjet	61	204	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1392	\N	\N	App\\Models\\IdeeProjet	61	203	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1393	\N	\N	App\\Models\\IdeeProjet	61	205	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1394	\N	\N	App\\Models\\IdeeProjet	61	208	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1395	\N	\N	App\\Models\\IdeeProjet	61	207	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1396	\N	\N	App\\Models\\IdeeProjet	61	206	2025-07-28 11:21:48	2025-07-28 11:21:48	\N
1397	"Modi labs sitfvfffl aliqffuidcaecat voluptatem aspernatur quam qui sunt ab in sed reprehenderit voluptatem"	\N	App\\Models\\IdeeProjet	62	161	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1398	"Omnis sit odit opt fffff fofs dumjgf est"	\N	App\\Models\\IdeeProjet	62	162	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1399	1	\N	App\\Models\\IdeeProjet	62	163	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1400	[87]	\N	App\\Models\\IdeeProjet	62	165	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1401	10	\N	App\\Models\\IdeeProjet	62	169	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1402	78	\N	App\\Models\\IdeeProjet	62	170	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1403	{"devise": "FCFA", "montant": 68}	\N	App\\Models\\IdeeProjet	62	166	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1404	3	\N	App\\Models\\IdeeProjet	62	168	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1405	[2]	\N	App\\Models\\IdeeProjet	62	176	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1406	[1]	\N	App\\Models\\IdeeProjet	62	177	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1407	19	\N	App\\Models\\IdeeProjet	62	171	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1408	20	\N	App\\Models\\IdeeProjet	62	172	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1409	27	\N	App\\Models\\IdeeProjet	62	173	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1410	[3]	\N	App\\Models\\IdeeProjet	62	174	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1411	[1]	\N	App\\Models\\IdeeProjet	62	175	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1412	[4]	\N	App\\Models\\IdeeProjet	62	182	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1413	[3]	\N	App\\Models\\IdeeProjet	62	183	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1414	[10]	\N	App\\Models\\IdeeProjet	62	184	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1415	[9]	\N	App\\Models\\IdeeProjet	62	178	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1416	[7]	\N	App\\Models\\IdeeProjet	62	212	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1417	[8]	\N	App\\Models\\IdeeProjet	62	180	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1418	[14]	\N	App\\Models\\IdeeProjet	62	181	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1419	[5]	\N	App\\Models\\IdeeProjet	62	179	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1420	["Ipsum voluptas sunt consequat Proident harum explicabo Deserunt accusantium dolor aut fuga Mollit iusto quia distinctio Incididunt unde vel"]	\N	App\\Models\\IdeeProjet	62	189	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1421	"Sint do delectus magnam quisquam labore"	\N	App\\Models\\IdeeProjet	62	213	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1422	[10]	\N	App\\Models\\IdeeProjet	62	187	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1423	"Elit rerum veniam quas quis excepteur sed dolore nihil ullam qui"	\N	App\\Models\\IdeeProjet	62	188	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1424	4	\N	App\\Models\\IdeeProjet	62	185	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1425	6	\N	App\\Models\\IdeeProjet	62	186	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1426	"Aut dolorem do non minus labore illum architecto in dignissimos ea quia qui"	\N	App\\Models\\IdeeProjet	62	196	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1427	"Fuga Voluptas dolor ut et pariatur Libero accusantium nulla magni duis qui nisi laudantium commodo iure incidunt"	\N	App\\Models\\IdeeProjet	62	197	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1428	"Nihil ut ea nihil recusandae Fugiat veritatis dolores nisi veritatis delectus ullamco perferendis do voluptatem Nulla non"	\N	App\\Models\\IdeeProjet	62	190	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1429	["Soluta minim cumque officia non aut ut laborum Nam corrupti quis labore voluptatem Id quia sed"]	\N	App\\Models\\IdeeProjet	62	191	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1430	["Dolor in omnis enim qui sint esse a aliquam deleniti aut magna"]	\N	App\\Models\\IdeeProjet	62	192	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1431	"Temporibus corporis dolor dolores veritatis ea error nisi vel esse occaecat impedit ipsam ut fugiat dolorem sint qui reprehenderit"	\N	App\\Models\\IdeeProjet	62	193	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1432	"Sint odit nobis sunt inventore a amet anim repudiandae dolorem laboris quos et a"	\N	App\\Models\\IdeeProjet	62	194	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1433	"Hic quibusdam qui eaque sapiente sit pariatur Harum non sunt aut perferendis aut commodo quam sint sit dolore cillum"	\N	App\\Models\\IdeeProjet	62	195	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1434	"Dolorum corporis accusamus error cum itaque repellendus Eos atque"	\N	App\\Models\\IdeeProjet	62	204	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1435	"Obcaecati ipsum ad nihil ut commodi dolore in deserunt"	\N	App\\Models\\IdeeProjet	62	201	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1436	"Velit assumenda accusantium eligendi blanditiis id quam"	\N	App\\Models\\IdeeProjet	62	207	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1437	"Voluptatem non voluptatem fugit ullam reprehenderit numquam commodi debitis facere et asperiores esse et"	\N	App\\Models\\IdeeProjet	62	208	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1438	"Dolorem in proident cumque hic sed corrupti obcaecati incidunt magnam vero velit ex sint nostrum"	\N	App\\Models\\IdeeProjet	62	202	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1439	"Suscipit dolores aliquid provident minim dignissimos voluptatem vitae magni itaque"	\N	App\\Models\\IdeeProjet	62	203	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1440	"Voluptates voluptate corrupti quis voluptas qui atque natus non provident eiusmod consequatur Dolore eius veniam molestiae"	\N	App\\Models\\IdeeProjet	62	198	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1441	"Facilis architecto ad consectetur quod tempor corrupti laboriosam optio"	\N	App\\Models\\IdeeProjet	62	200	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1442	"Qui ducimus commodi nisi maxime consequatur veniam nihil"	\N	App\\Models\\IdeeProjet	62	199	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1443	"Iste officia tempore ut beatae et repudiandae aliqua Eu distinctio Ut natus laborum ea deserunt duis"	\N	App\\Models\\IdeeProjet	62	205	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1444	"Sit sed debitis sapiente aut dolore aliquid culpa ab cum maxime quia in quos placeat deleniti"	\N	App\\Models\\IdeeProjet	62	206	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
1445	"Eaque anim libero iusto in minim amet exercitation est mollit reprehenderit"	\N	App\\Models\\IdeeProjet	63	161	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1446	"Voluptatum amet temporibus incididunt cupiditate"	\N	App\\Models\\IdeeProjet	63	162	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1447	\N	\N	App\\Models\\IdeeProjet	63	163	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1448	[92]	\N	App\\Models\\IdeeProjet	63	165	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1449	80	\N	App\\Models\\IdeeProjet	63	169	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1450	29	\N	App\\Models\\IdeeProjet	63	170	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1451	{"devise": "FCFA", "montant": 45}	\N	App\\Models\\IdeeProjet	63	166	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1452	34	\N	App\\Models\\IdeeProjet	63	168	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1453	\N	\N	App\\Models\\IdeeProjet	63	171	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1454	\N	\N	App\\Models\\IdeeProjet	63	172	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1455	\N	\N	App\\Models\\IdeeProjet	63	173	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1456	[]	\N	App\\Models\\IdeeProjet	63	174	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1457	[]	\N	App\\Models\\IdeeProjet	63	175	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1458	[]	\N	App\\Models\\IdeeProjet	63	176	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1459	[]	\N	App\\Models\\IdeeProjet	63	177	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1460	[]	\N	App\\Models\\IdeeProjet	63	178	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1461	[]	\N	App\\Models\\IdeeProjet	63	179	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1462	[]	\N	App\\Models\\IdeeProjet	63	180	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1463	[]	\N	App\\Models\\IdeeProjet	63	181	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1464	[]	\N	App\\Models\\IdeeProjet	63	182	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1465	[]	\N	App\\Models\\IdeeProjet	63	212	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1466	[]	\N	App\\Models\\IdeeProjet	63	183	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1467	[]	\N	App\\Models\\IdeeProjet	63	184	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1468	\N	\N	App\\Models\\IdeeProjet	63	185	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1469	\N	\N	App\\Models\\IdeeProjet	63	186	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1470	[]	\N	App\\Models\\IdeeProjet	63	187	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1471	\N	\N	App\\Models\\IdeeProjet	63	188	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1472	\N	\N	App\\Models\\IdeeProjet	63	213	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1473	[]	\N	App\\Models\\IdeeProjet	63	189	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1474	\N	\N	App\\Models\\IdeeProjet	63	190	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1475	[]	\N	App\\Models\\IdeeProjet	63	191	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1476	[]	\N	App\\Models\\IdeeProjet	63	192	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1477	\N	\N	App\\Models\\IdeeProjet	63	193	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1478	\N	\N	App\\Models\\IdeeProjet	63	194	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1479	\N	\N	App\\Models\\IdeeProjet	63	195	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1480	\N	\N	App\\Models\\IdeeProjet	63	196	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1481	\N	\N	App\\Models\\IdeeProjet	63	197	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1482	\N	\N	App\\Models\\IdeeProjet	63	198	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1483	\N	\N	App\\Models\\IdeeProjet	63	199	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1484	\N	\N	App\\Models\\IdeeProjet	63	200	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1485	\N	\N	App\\Models\\IdeeProjet	63	201	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1486	\N	\N	App\\Models\\IdeeProjet	63	202	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1487	\N	\N	App\\Models\\IdeeProjet	63	204	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1488	\N	\N	App\\Models\\IdeeProjet	63	203	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1489	\N	\N	App\\Models\\IdeeProjet	63	205	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1490	\N	\N	App\\Models\\IdeeProjet	63	208	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1491	\N	\N	App\\Models\\IdeeProjet	63	207	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1492	\N	\N	App\\Models\\IdeeProjet	63	206	2025-07-28 11:35:24	2025-07-28 11:35:24	\N
1493	"Sint earum ut dignissimos quae ad nulla quo eum duis pariatur Magna et sed voluptas ad"	\N	App\\Models\\IdeeProjet	64	161	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1494	"Deserunt voluptatem Veniam itaque consequatur I"	\N	App\\Models\\IdeeProjet	64	162	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1495	1	\N	App\\Models\\IdeeProjet	64	163	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1496	[71]	\N	App\\Models\\IdeeProjet	64	165	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1497	34	\N	App\\Models\\IdeeProjet	64	169	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1498	17	\N	App\\Models\\IdeeProjet	64	170	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1499	{"devise": "FCFA", "montant": 46}	\N	App\\Models\\IdeeProjet	64	166	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1500	73	\N	App\\Models\\IdeeProjet	64	168	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1501	\N	\N	App\\Models\\IdeeProjet	64	171	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1502	\N	\N	App\\Models\\IdeeProjet	64	172	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1503	\N	\N	App\\Models\\IdeeProjet	64	173	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1504	[]	\N	App\\Models\\IdeeProjet	64	174	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1505	[]	\N	App\\Models\\IdeeProjet	64	175	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1506	[]	\N	App\\Models\\IdeeProjet	64	176	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1507	[]	\N	App\\Models\\IdeeProjet	64	177	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1508	[]	\N	App\\Models\\IdeeProjet	64	178	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1509	[]	\N	App\\Models\\IdeeProjet	64	179	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1510	[]	\N	App\\Models\\IdeeProjet	64	180	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1511	[]	\N	App\\Models\\IdeeProjet	64	181	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1512	[]	\N	App\\Models\\IdeeProjet	64	182	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1513	[]	\N	App\\Models\\IdeeProjet	64	212	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1514	[]	\N	App\\Models\\IdeeProjet	64	183	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1515	[]	\N	App\\Models\\IdeeProjet	64	184	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1516	[]	\N	App\\Models\\IdeeProjet	64	185	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1517	[]	\N	App\\Models\\IdeeProjet	64	186	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1518	[]	\N	App\\Models\\IdeeProjet	64	187	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1519	\N	\N	App\\Models\\IdeeProjet	64	188	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1520	\N	\N	App\\Models\\IdeeProjet	64	213	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1521	[]	\N	App\\Models\\IdeeProjet	64	189	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1522	\N	\N	App\\Models\\IdeeProjet	64	190	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1523	[]	\N	App\\Models\\IdeeProjet	64	191	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1524	[]	\N	App\\Models\\IdeeProjet	64	192	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1525	\N	\N	App\\Models\\IdeeProjet	64	193	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1526	\N	\N	App\\Models\\IdeeProjet	64	194	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1527	\N	\N	App\\Models\\IdeeProjet	64	195	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1528	\N	\N	App\\Models\\IdeeProjet	64	196	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1529	\N	\N	App\\Models\\IdeeProjet	64	197	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1530	\N	\N	App\\Models\\IdeeProjet	64	198	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1531	\N	\N	App\\Models\\IdeeProjet	64	199	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1532	\N	\N	App\\Models\\IdeeProjet	64	200	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1533	\N	\N	App\\Models\\IdeeProjet	64	201	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1534	\N	\N	App\\Models\\IdeeProjet	64	202	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1535	\N	\N	App\\Models\\IdeeProjet	64	204	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1536	\N	\N	App\\Models\\IdeeProjet	64	203	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1537	\N	\N	App\\Models\\IdeeProjet	64	205	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1538	\N	\N	App\\Models\\IdeeProjet	64	208	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1539	\N	\N	App\\Models\\IdeeProjet	64	207	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1540	\N	\N	App\\Models\\IdeeProjet	64	206	2025-07-28 11:45:59	2025-07-28 11:45:59	\N
1541	"onsequaturdre um"	\N	App\\Models\\IdeeProjet	65	161	2025-07-28 11:48:02	2025-07-28 11:48:02	\N
1542	"Fuga Nihil enim non non quia laboriosam ut deserunt ratione"	\N	App\\Models\\IdeeProjet	66	161	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1543	"Sint est nesciunt rerum fugiat inventore velit la"	\N	App\\Models\\IdeeProjet	66	162	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1544	\N	\N	App\\Models\\IdeeProjet	66	163	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1545	[8]	\N	App\\Models\\IdeeProjet	66	165	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1546	5	\N	App\\Models\\IdeeProjet	66	169	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1547	95	\N	App\\Models\\IdeeProjet	66	170	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1548	{"devise": "FCFA", "montant": 60}	\N	App\\Models\\IdeeProjet	66	166	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1549	35	\N	App\\Models\\IdeeProjet	66	168	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1550	\N	\N	App\\Models\\IdeeProjet	66	171	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1551	\N	\N	App\\Models\\IdeeProjet	66	172	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1552	\N	\N	App\\Models\\IdeeProjet	66	173	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1553	[]	\N	App\\Models\\IdeeProjet	66	174	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1554	[]	\N	App\\Models\\IdeeProjet	66	175	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1555	[]	\N	App\\Models\\IdeeProjet	66	176	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1556	[]	\N	App\\Models\\IdeeProjet	66	177	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1557	[]	\N	App\\Models\\IdeeProjet	66	178	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1558	[]	\N	App\\Models\\IdeeProjet	66	179	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1559	[]	\N	App\\Models\\IdeeProjet	66	180	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1560	[]	\N	App\\Models\\IdeeProjet	66	181	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1561	[]	\N	App\\Models\\IdeeProjet	66	182	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1562	[]	\N	App\\Models\\IdeeProjet	66	212	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1563	[]	\N	App\\Models\\IdeeProjet	66	183	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1564	[]	\N	App\\Models\\IdeeProjet	66	184	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1565	[]	\N	App\\Models\\IdeeProjet	66	185	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1566	[]	\N	App\\Models\\IdeeProjet	66	186	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1567	[]	\N	App\\Models\\IdeeProjet	66	187	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1568	\N	\N	App\\Models\\IdeeProjet	66	188	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1569	\N	\N	App\\Models\\IdeeProjet	66	213	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1570	[]	\N	App\\Models\\IdeeProjet	66	189	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1571	\N	\N	App\\Models\\IdeeProjet	66	190	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1572	[]	\N	App\\Models\\IdeeProjet	66	191	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1573	[]	\N	App\\Models\\IdeeProjet	66	192	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1574	\N	\N	App\\Models\\IdeeProjet	66	193	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1575	\N	\N	App\\Models\\IdeeProjet	66	194	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1576	\N	\N	App\\Models\\IdeeProjet	66	195	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1577	\N	\N	App\\Models\\IdeeProjet	66	196	2025-07-28 11:50:07	2025-07-28 11:52:43	\N
1578	\N	\N	App\\Models\\IdeeProjet	66	197	2025-07-28 11:50:07	2025-07-28 11:52:44	\N
1579	\N	\N	App\\Models\\IdeeProjet	66	198	2025-07-28 11:50:07	2025-07-28 11:52:44	\N
1580	\N	\N	App\\Models\\IdeeProjet	66	199	2025-07-28 11:50:07	2025-07-28 11:52:44	\N
1581	\N	\N	App\\Models\\IdeeProjet	66	200	2025-07-28 11:50:07	2025-07-28 11:52:44	\N
1582	\N	\N	App\\Models\\IdeeProjet	66	201	2025-07-28 11:50:07	2025-07-28 11:52:44	\N
1583	\N	\N	App\\Models\\IdeeProjet	66	202	2025-07-28 11:50:07	2025-07-28 11:52:44	\N
1584	\N	\N	App\\Models\\IdeeProjet	66	204	2025-07-28 11:50:07	2025-07-28 11:52:44	\N
1585	\N	\N	App\\Models\\IdeeProjet	66	203	2025-07-28 11:50:07	2025-07-28 11:52:44	\N
1586	\N	\N	App\\Models\\IdeeProjet	66	205	2025-07-28 11:50:07	2025-07-28 11:52:44	\N
1587	\N	\N	App\\Models\\IdeeProjet	66	208	2025-07-28 11:50:07	2025-07-28 11:52:44	\N
1588	\N	\N	App\\Models\\IdeeProjet	66	207	2025-07-28 11:50:07	2025-07-28 11:52:44	\N
1589	\N	\N	App\\Models\\IdeeProjet	66	206	2025-07-28 11:50:07	2025-07-28 11:52:44	\N
1590	"medoski"	\N	App\\Models\\IdeeProjet	67	161	2025-07-28 11:52:45	2025-07-28 11:53:09	\N
1591	"Aut reprehenderit nemo voluptas nisi occaecat acc"	\N	App\\Models\\IdeeProjet	67	162	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1592	1	\N	App\\Models\\IdeeProjet	67	163	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1593	[35]	\N	App\\Models\\IdeeProjet	67	165	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1594	49	\N	App\\Models\\IdeeProjet	67	169	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1595	64	\N	App\\Models\\IdeeProjet	67	170	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1596	{"devise": "FCFA", "montant": 42}	\N	App\\Models\\IdeeProjet	67	166	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1597	21	\N	App\\Models\\IdeeProjet	67	168	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1598	\N	\N	App\\Models\\IdeeProjet	67	171	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1599	\N	\N	App\\Models\\IdeeProjet	67	172	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1600	\N	\N	App\\Models\\IdeeProjet	67	173	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1601	[]	\N	App\\Models\\IdeeProjet	67	174	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1602	[]	\N	App\\Models\\IdeeProjet	67	175	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1603	[]	\N	App\\Models\\IdeeProjet	67	176	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1604	[]	\N	App\\Models\\IdeeProjet	67	177	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1605	[]	\N	App\\Models\\IdeeProjet	67	178	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1606	[]	\N	App\\Models\\IdeeProjet	67	179	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1607	[]	\N	App\\Models\\IdeeProjet	67	180	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1608	[]	\N	App\\Models\\IdeeProjet	67	181	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1609	[]	\N	App\\Models\\IdeeProjet	67	182	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1610	[]	\N	App\\Models\\IdeeProjet	67	212	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1611	[]	\N	App\\Models\\IdeeProjet	67	183	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1612	[]	\N	App\\Models\\IdeeProjet	67	184	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1613	[]	\N	App\\Models\\IdeeProjet	67	185	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1614	[]	\N	App\\Models\\IdeeProjet	67	186	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1615	[]	\N	App\\Models\\IdeeProjet	67	187	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1616	\N	\N	App\\Models\\IdeeProjet	67	188	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1617	\N	\N	App\\Models\\IdeeProjet	67	213	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1618	[]	\N	App\\Models\\IdeeProjet	67	189	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1619	\N	\N	App\\Models\\IdeeProjet	67	190	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1620	[]	\N	App\\Models\\IdeeProjet	67	191	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1621	[]	\N	App\\Models\\IdeeProjet	67	192	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1622	\N	\N	App\\Models\\IdeeProjet	67	193	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1623	\N	\N	App\\Models\\IdeeProjet	67	194	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1624	\N	\N	App\\Models\\IdeeProjet	67	195	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1625	\N	\N	App\\Models\\IdeeProjet	67	196	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1626	\N	\N	App\\Models\\IdeeProjet	67	197	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1627	\N	\N	App\\Models\\IdeeProjet	67	198	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1628	\N	\N	App\\Models\\IdeeProjet	67	199	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1629	\N	\N	App\\Models\\IdeeProjet	67	200	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1630	\N	\N	App\\Models\\IdeeProjet	67	201	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1631	\N	\N	App\\Models\\IdeeProjet	67	202	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1632	\N	\N	App\\Models\\IdeeProjet	67	204	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1633	\N	\N	App\\Models\\IdeeProjet	67	203	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1634	\N	\N	App\\Models\\IdeeProjet	67	205	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1635	\N	\N	App\\Models\\IdeeProjet	67	208	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1636	\N	\N	App\\Models\\IdeeProjet	67	207	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1637	\N	\N	App\\Models\\IdeeProjet	67	206	2025-07-28 11:53:09	2025-07-28 11:53:09	\N
1638	"onsequatugfrdre um"	\N	App\\Models\\IdeeProjet	68	161	2025-07-28 11:56:01	2025-07-28 11:56:01	\N
1640	"Sunt quam obcaecati velit culpa magni libero"	\N	App\\Models\\IdeeProjet	69	162	2025-07-28 13:34:21	2025-07-28 13:34:21	\N
1641	1	\N	App\\Models\\IdeeProjet	69	163	2025-07-28 13:34:21	2025-07-28 13:34:21	\N
1668	"Velit blanditiis quia molestiae consequatur Sunt sunt aute aut quisquam et tempore aut doloribus"	\N	App\\Models\\IdeeProjet	69	190	2025-07-28 13:34:21	2025-07-28 13:34:21	\N
1676	"Architecto neque non provident aliquam ut sint et"	\N	App\\Models\\IdeeProjet	69	198	2025-07-28 13:34:21	2025-07-28 13:34:21	\N
1687	"onsequatugfrvhgh dre um"	\N	App\\Models\\IdeeProjet	70	161	2025-07-28 13:51:28	2025-07-28 13:51:28	\N
1647	\N	\N	App\\Models\\IdeeProjet	69	171	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1648	\N	\N	App\\Models\\IdeeProjet	69	172	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1649	\N	\N	App\\Models\\IdeeProjet	69	173	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1650	[]	\N	App\\Models\\IdeeProjet	69	174	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1651	[]	\N	App\\Models\\IdeeProjet	69	175	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1652	[]	\N	App\\Models\\IdeeProjet	69	176	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1653	[]	\N	App\\Models\\IdeeProjet	69	177	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1654	[]	\N	App\\Models\\IdeeProjet	69	178	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1655	[]	\N	App\\Models\\IdeeProjet	69	179	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1656	[]	\N	App\\Models\\IdeeProjet	69	180	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1657	[]	\N	App\\Models\\IdeeProjet	69	181	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1658	[]	\N	App\\Models\\IdeeProjet	69	182	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1659	[]	\N	App\\Models\\IdeeProjet	69	212	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1660	[]	\N	App\\Models\\IdeeProjet	69	183	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1661	[]	\N	App\\Models\\IdeeProjet	69	184	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1662	[]	\N	App\\Models\\IdeeProjet	69	185	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1663	[]	\N	App\\Models\\IdeeProjet	69	186	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1664	[]	\N	App\\Models\\IdeeProjet	69	187	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1665	\N	\N	App\\Models\\IdeeProjet	69	188	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1666	\N	\N	App\\Models\\IdeeProjet	69	213	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1667	[]	\N	App\\Models\\IdeeProjet	69	189	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1669	[]	\N	App\\Models\\IdeeProjet	69	191	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1670	[]	\N	App\\Models\\IdeeProjet	69	192	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1671	\N	\N	App\\Models\\IdeeProjet	69	193	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1672	\N	\N	App\\Models\\IdeeProjet	69	194	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1673	\N	\N	App\\Models\\IdeeProjet	69	195	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1674	\N	\N	App\\Models\\IdeeProjet	69	196	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1675	\N	\N	App\\Models\\IdeeProjet	69	197	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1677	\N	\N	App\\Models\\IdeeProjet	69	199	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1678	\N	\N	App\\Models\\IdeeProjet	69	200	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1679	\N	\N	App\\Models\\IdeeProjet	69	201	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1680	\N	\N	App\\Models\\IdeeProjet	69	202	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1681	\N	\N	App\\Models\\IdeeProjet	69	204	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1682	\N	\N	App\\Models\\IdeeProjet	69	203	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1683	\N	\N	App\\Models\\IdeeProjet	69	205	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1684	\N	\N	App\\Models\\IdeeProjet	69	208	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1685	\N	\N	App\\Models\\IdeeProjet	69	207	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1686	\N	\N	App\\Models\\IdeeProjet	69	206	2025-07-28 13:34:21	2025-07-28 14:47:27	\N
1639	"Perspiciatis numuam dolre voluptates dolore quo ullam magnam blanditiis sapiente sunt aut deleniti id quibusdam esse cillum"	\N	App\\Models\\IdeeProjet	69	161	2025-07-28 13:34:21	2025-07-28 15:05:34	\N
1642	[7]	\N	App\\Models\\IdeeProjet	69	165	2025-07-28 13:34:21	2025-07-28 15:05:34	\N
1643	70	\N	App\\Models\\IdeeProjet	69	169	2025-07-28 13:34:21	2025-07-28 15:05:34	\N
1644	85	\N	App\\Models\\IdeeProjet	69	170	2025-07-28 13:34:21	2025-07-28 15:05:34	\N
1645	{"devise": "FCFA", "montant": 3}	\N	App\\Models\\IdeeProjet	69	166	2025-07-28 13:34:21	2025-07-28 15:05:34	\N
1646	7	\N	App\\Models\\IdeeProjet	69	168	2025-07-28 13:34:21	2025-07-28 15:05:34	\N
1688	"Molestias rem dicta neschhhiunt ipsum ipsum dolor itaque vel laboris repellendus Minus consequatur officia"	\N	App\\Models\\IdeeProjet	71	161	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1689	"Eos id aute aute enim aliquip"	\N	App\\Models\\IdeeProjet	71	162	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1690	1	\N	App\\Models\\IdeeProjet	71	163	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1691	[11]	\N	App\\Models\\IdeeProjet	71	165	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1692	40	\N	App\\Models\\IdeeProjet	71	169	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1693	72	\N	App\\Models\\IdeeProjet	71	170	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1694	{"devise": "FCFA", "montant": 25}	\N	App\\Models\\IdeeProjet	71	166	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1695	84	\N	App\\Models\\IdeeProjet	71	168	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1696	\N	\N	App\\Models\\IdeeProjet	71	171	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1697	\N	\N	App\\Models\\IdeeProjet	71	172	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1698	\N	\N	App\\Models\\IdeeProjet	71	173	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1699	[]	\N	App\\Models\\IdeeProjet	71	174	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1700	[]	\N	App\\Models\\IdeeProjet	71	175	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1701	[]	\N	App\\Models\\IdeeProjet	71	176	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1702	[]	\N	App\\Models\\IdeeProjet	71	177	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1703	[]	\N	App\\Models\\IdeeProjet	71	178	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1704	[]	\N	App\\Models\\IdeeProjet	71	179	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1705	[]	\N	App\\Models\\IdeeProjet	71	180	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1706	[]	\N	App\\Models\\IdeeProjet	71	181	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1707	[]	\N	App\\Models\\IdeeProjet	71	182	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1708	[]	\N	App\\Models\\IdeeProjet	71	212	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1709	[]	\N	App\\Models\\IdeeProjet	71	183	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1710	[]	\N	App\\Models\\IdeeProjet	71	184	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1711	[]	\N	App\\Models\\IdeeProjet	71	185	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1712	[]	\N	App\\Models\\IdeeProjet	71	186	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1713	[]	\N	App\\Models\\IdeeProjet	71	187	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1714	\N	\N	App\\Models\\IdeeProjet	71	188	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1715	\N	\N	App\\Models\\IdeeProjet	71	213	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1716	[]	\N	App\\Models\\IdeeProjet	71	189	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1717	\N	\N	App\\Models\\IdeeProjet	71	190	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1718	[]	\N	App\\Models\\IdeeProjet	71	191	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1719	[]	\N	App\\Models\\IdeeProjet	71	192	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1720	\N	\N	App\\Models\\IdeeProjet	71	193	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1721	\N	\N	App\\Models\\IdeeProjet	71	194	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1722	\N	\N	App\\Models\\IdeeProjet	71	195	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1723	\N	\N	App\\Models\\IdeeProjet	71	196	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1724	\N	\N	App\\Models\\IdeeProjet	71	197	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1725	\N	\N	App\\Models\\IdeeProjet	71	198	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1726	\N	\N	App\\Models\\IdeeProjet	71	199	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1727	\N	\N	App\\Models\\IdeeProjet	71	200	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1728	\N	\N	App\\Models\\IdeeProjet	71	201	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1729	\N	\N	App\\Models\\IdeeProjet	71	202	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1730	\N	\N	App\\Models\\IdeeProjet	71	204	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1731	\N	\N	App\\Models\\IdeeProjet	71	203	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1732	\N	\N	App\\Models\\IdeeProjet	71	205	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1733	\N	\N	App\\Models\\IdeeProjet	71	208	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1734	\N	\N	App\\Models\\IdeeProjet	71	207	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1735	\N	\N	App\\Models\\IdeeProjet	71	206	2025-07-28 15:06:06	2025-07-28 15:06:06	\N
1736	"onsequatugfrvhgh dre um"	\N	App\\Models\\IdeeProjet	72	161	2025-07-29 06:15:41	2025-07-29 06:15:41	\N
1737	"gfj"	\N	App\\Models\\IdeeProjet	73	161	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1738	"dsfsd"	\N	App\\Models\\IdeeProjet	73	162	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1739	1	\N	App\\Models\\IdeeProjet	73	163	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1740	[21]	\N	App\\Models\\IdeeProjet	73	165	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1741	22	\N	App\\Models\\IdeeProjet	73	169	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1742	22	\N	App\\Models\\IdeeProjet	73	170	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1743	{"devise": "FCFA", "montant": 22}	\N	App\\Models\\IdeeProjet	73	166	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1744	22	\N	App\\Models\\IdeeProjet	73	168	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1745	19	\N	App\\Models\\IdeeProjet	73	171	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1746	20	\N	App\\Models\\IdeeProjet	73	172	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1747	27	\N	App\\Models\\IdeeProjet	73	173	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1748	[3]	\N	App\\Models\\IdeeProjet	73	174	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1749	[17]	\N	App\\Models\\IdeeProjet	73	175	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1750	[109]	\N	App\\Models\\IdeeProjet	73	176	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1751	[]	\N	App\\Models\\IdeeProjet	73	177	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1752	[11]	\N	App\\Models\\IdeeProjet	73	178	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1753	[5]	\N	App\\Models\\IdeeProjet	73	179	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1754	[8]	\N	App\\Models\\IdeeProjet	73	180	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1755	[14]	\N	App\\Models\\IdeeProjet	73	181	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1756	[4]	\N	App\\Models\\IdeeProjet	73	182	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1757	[7]	\N	App\\Models\\IdeeProjet	73	212	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1758	[3]	\N	App\\Models\\IdeeProjet	73	183	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1759	[10]	\N	App\\Models\\IdeeProjet	73	184	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1760	[4]	\N	App\\Models\\IdeeProjet	73	185	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1761	[6]	\N	App\\Models\\IdeeProjet	73	186	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1762	[14]	\N	App\\Models\\IdeeProjet	73	187	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1763	"uii"	\N	App\\Models\\IdeeProjet	73	188	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1764	"uiui"	\N	App\\Models\\IdeeProjet	73	213	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1765	["iuu"]	\N	App\\Models\\IdeeProjet	73	189	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1766	"uy"	\N	App\\Models\\IdeeProjet	73	190	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1767	["uuy"]	\N	App\\Models\\IdeeProjet	73	191	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1768	["yyu"]	\N	App\\Models\\IdeeProjet	73	192	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1769	"yuy"	\N	App\\Models\\IdeeProjet	73	193	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1770	"uyy"	\N	App\\Models\\IdeeProjet	73	194	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1771	"yuu"	\N	App\\Models\\IdeeProjet	73	195	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1772	"yuuuy"	\N	App\\Models\\IdeeProjet	73	196	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1773	"yuyu"	\N	App\\Models\\IdeeProjet	73	197	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1774	"rty"	\N	App\\Models\\IdeeProjet	73	198	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1775	"yrtr"	\N	App\\Models\\IdeeProjet	73	199	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1776	"tyrty"	\N	App\\Models\\IdeeProjet	73	200	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1777	"tyrt"	\N	App\\Models\\IdeeProjet	73	201	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1778	"ytrt"	\N	App\\Models\\IdeeProjet	73	202	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1779	"rtyr"	\N	App\\Models\\IdeeProjet	73	204	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1780	"tryr"	\N	App\\Models\\IdeeProjet	73	203	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1781	"ytr"	\N	App\\Models\\IdeeProjet	73	205	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1782	"rytr"	\N	App\\Models\\IdeeProjet	73	208	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1783	"yrytr"	\N	App\\Models\\IdeeProjet	73	207	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1784	"ytrtrrty"	\N	App\\Models\\IdeeProjet	73	206	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
1786	"rtrtr"	\N	App\\Models\\IdeeProjet	74	162	2025-07-29 09:01:15	2025-07-29 09:01:15	\N
1787	1	\N	App\\Models\\IdeeProjet	74	163	2025-07-29 09:01:15	2025-07-29 09:01:15	\N
1788	[23]	\N	App\\Models\\IdeeProjet	74	165	2025-07-29 09:01:15	2025-07-29 09:01:15	\N
1814	"jk"	\N	App\\Models\\IdeeProjet	74	190	2025-07-29 09:01:15	2025-07-29 09:01:15	\N
1822	"hjh"	\N	App\\Models\\IdeeProjet	74	198	2025-07-29 09:01:16	2025-07-29 09:01:16	\N
1785	"tretruiuiuiui"	\N	App\\Models\\IdeeProjet	74	161	2025-07-29 09:01:15	2025-07-29 09:02:04	\N
1789	"12.00"	\N	App\\Models\\IdeeProjet	74	169	2025-07-29 09:01:15	2025-07-29 09:02:04	\N
1790	"13.00"	\N	App\\Models\\IdeeProjet	74	170	2025-07-29 09:01:15	2025-07-29 09:02:04	\N
1791	{"devise": "FCFA", "montant": 12}	\N	App\\Models\\IdeeProjet	74	166	2025-07-29 09:01:15	2025-07-29 09:02:04	\N
1792	"12.00"	\N	App\\Models\\IdeeProjet	74	168	2025-07-29 09:01:15	2025-07-29 09:02:04	\N
1793	\N	\N	App\\Models\\IdeeProjet	74	171	2025-07-29 09:01:15	2025-07-29 09:02:04	\N
1794	\N	\N	App\\Models\\IdeeProjet	74	172	2025-07-29 09:01:15	2025-07-29 09:02:04	\N
1795	\N	\N	App\\Models\\IdeeProjet	74	173	2025-07-29 09:01:15	2025-07-29 09:02:04	\N
1796	[]	\N	App\\Models\\IdeeProjet	74	174	2025-07-29 09:01:15	2025-07-29 09:02:04	\N
1797	[]	\N	App\\Models\\IdeeProjet	74	175	2025-07-29 09:01:15	2025-07-29 09:02:04	\N
1798	[]	\N	App\\Models\\IdeeProjet	74	176	2025-07-29 09:01:15	2025-07-29 09:02:04	\N
1799	[]	\N	App\\Models\\IdeeProjet	74	177	2025-07-29 09:01:15	2025-07-29 09:02:04	\N
1800	[]	\N	App\\Models\\IdeeProjet	74	178	2025-07-29 09:01:15	2025-07-29 09:02:04	\N
1801	[]	\N	App\\Models\\IdeeProjet	74	179	2025-07-29 09:01:15	2025-07-29 09:02:04	\N
1802	[]	\N	App\\Models\\IdeeProjet	74	180	2025-07-29 09:01:15	2025-07-29 09:02:04	\N
1803	[]	\N	App\\Models\\IdeeProjet	74	181	2025-07-29 09:01:15	2025-07-29 09:02:04	\N
1804	[]	\N	App\\Models\\IdeeProjet	74	182	2025-07-29 09:01:15	2025-07-29 09:02:04	\N
1805	[]	\N	App\\Models\\IdeeProjet	74	212	2025-07-29 09:01:15	2025-07-29 09:02:04	\N
1806	[]	\N	App\\Models\\IdeeProjet	74	183	2025-07-29 09:01:15	2025-07-29 09:02:04	\N
1807	[]	\N	App\\Models\\IdeeProjet	74	184	2025-07-29 09:01:15	2025-07-29 09:02:04	\N
1808	[]	\N	App\\Models\\IdeeProjet	74	185	2025-07-29 09:01:15	2025-07-29 09:02:04	\N
1809	[]	\N	App\\Models\\IdeeProjet	74	186	2025-07-29 09:01:15	2025-07-29 09:02:04	\N
1810	[]	\N	App\\Models\\IdeeProjet	74	187	2025-07-29 09:01:15	2025-07-29 09:02:04	\N
1811	\N	\N	App\\Models\\IdeeProjet	74	188	2025-07-29 09:01:15	2025-07-29 09:02:04	\N
1812	\N	\N	App\\Models\\IdeeProjet	74	213	2025-07-29 09:01:15	2025-07-29 09:02:04	\N
1813	[]	\N	App\\Models\\IdeeProjet	74	189	2025-07-29 09:01:15	2025-07-29 09:02:04	\N
1815	[]	\N	App\\Models\\IdeeProjet	74	191	2025-07-29 09:01:15	2025-07-29 09:02:04	\N
1816	[]	\N	App\\Models\\IdeeProjet	74	192	2025-07-29 09:01:16	2025-07-29 09:02:04	\N
1817	\N	\N	App\\Models\\IdeeProjet	74	193	2025-07-29 09:01:16	2025-07-29 09:02:04	\N
1818	\N	\N	App\\Models\\IdeeProjet	74	194	2025-07-29 09:01:16	2025-07-29 09:02:04	\N
1819	\N	\N	App\\Models\\IdeeProjet	74	195	2025-07-29 09:01:16	2025-07-29 09:02:04	\N
1820	\N	\N	App\\Models\\IdeeProjet	74	196	2025-07-29 09:01:16	2025-07-29 09:02:04	\N
1821	\N	\N	App\\Models\\IdeeProjet	74	197	2025-07-29 09:01:16	2025-07-29 09:02:04	\N
1823	\N	\N	App\\Models\\IdeeProjet	74	199	2025-07-29 09:01:16	2025-07-29 09:02:04	\N
1824	\N	\N	App\\Models\\IdeeProjet	74	200	2025-07-29 09:01:16	2025-07-29 09:02:04	\N
1825	\N	\N	App\\Models\\IdeeProjet	74	201	2025-07-29 09:01:16	2025-07-29 09:02:04	\N
1826	\N	\N	App\\Models\\IdeeProjet	74	202	2025-07-29 09:01:16	2025-07-29 09:02:04	\N
1827	\N	\N	App\\Models\\IdeeProjet	74	204	2025-07-29 09:01:16	2025-07-29 09:02:04	\N
1828	\N	\N	App\\Models\\IdeeProjet	74	203	2025-07-29 09:01:16	2025-07-29 09:02:04	\N
1829	\N	\N	App\\Models\\IdeeProjet	74	205	2025-07-29 09:01:16	2025-07-29 09:02:04	\N
1830	\N	\N	App\\Models\\IdeeProjet	74	208	2025-07-29 09:01:16	2025-07-29 09:02:04	\N
1831	\N	\N	App\\Models\\IdeeProjet	74	207	2025-07-29 09:01:16	2025-07-29 09:02:04	\N
1832	\N	\N	App\\Models\\IdeeProjet	74	206	2025-07-29 09:01:16	2025-07-29 09:02:04	\N
\.


--
-- Data for Name: champs_sections; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.champs_sections (id, intitule, description, slug, ordre_affichage, type, "documentId", created_at, updated_at, deleted_at) FROM stdin;
38	Informations Générales		informations-générales	1	formulaire	40	2025-07-23 05:18:58	2025-07-24 20:26:52	\N
40	Cadres stratégiques		cadres-stratégiques	3	formulaire	40	2025-07-23 05:18:58	2025-07-24 20:26:52	\N
41	Financement et Bénéficiaires		financement-et-bénéficiaires	4	formulaire	40	2025-07-23 05:18:58	2025-07-24 20:26:53	\N
42	Contexte et Analyse		contexte-et-analyse	5	formulaire	40	2025-07-23 05:18:58	2025-07-24 20:26:53	\N
43	Description technique et Impacts		description-technique-et-impacts	6	formulaire	40	2025-07-23 05:18:58	2025-07-24 20:26:53	\N
39	Secteur d\\'activité et Localisation		secteur-d'activité-et-localisation	2	formulaire	40	2025-07-23 05:18:58	2025-07-25 10:47:43	\N
\.


--
-- Data for Name: cibles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cibles (id, cible, slug, created_at, updated_at, deleted_at) FROM stdin;
1	1752834118::Cibles - 01	1752834118::cibles---01	2025-07-18 05:26:31	2025-07-18 10:21:58	2025-07-18 10:21:58
3	1753116552::Cibles - 01	1753116552::cibles---01	2025-07-21 16:32:37	2025-07-21 16:49:12	2025-07-21 16:49:12
4	kPubliccible	kpubliccible	2025-07-21 17:06:40	2025-07-21 17:06:40	\N
2	1753121969::Cibles  01	1753121969::cibles--01	2025-07-19 08:04:09	2025-07-21 18:19:29	2025-07-21 18:19:29
5	Cibles  01	cibles--01	2025-07-21 18:18:44	2025-07-21 18:19:52	\N
\.


--
-- Data for Name: cibles_projets; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cibles_projets (id, "cibleId", projetable_type, projetable_id, created_at, updated_at, deleted_at) FROM stdin;
2	4	App\\Models\\IdeeProjet	15	2025-07-23 09:31:16	2025-07-23 09:31:16	\N
4	4	App\\Models\\IdeeProjet	17	2025-07-23 09:56:49	2025-07-23 09:56:49	\N
6	4	App\\Models\\IdeeProjet	22	2025-07-23 11:09:47	2025-07-23 11:09:47	\N
7	4	App\\Models\\IdeeProjet	23	2025-07-23 17:29:21	2025-07-23 17:29:21	\N
8	4	App\\Models\\IdeeProjet	24	2025-07-23 17:40:46	2025-07-23 17:40:46	\N
9	4	App\\Models\\IdeeProjet	25	2025-07-23 18:35:39	2025-07-23 18:35:39	\N
10	4	App\\Models\\IdeeProjet	26	2025-07-23 21:52:58	2025-07-23 21:52:58	\N
11	4	App\\Models\\IdeeProjet	27	2025-07-23 21:58:00	2025-07-23 21:58:00	\N
12	4	App\\Models\\IdeeProjet	28	2025-07-23 22:00:07	2025-07-23 22:00:07	\N
13	4	App\\Models\\IdeeProjet	29	2025-07-23 22:13:54	2025-07-23 22:13:54	\N
14	4	App\\Models\\IdeeProjet	30	2025-07-23 22:56:32	2025-07-23 22:56:32	\N
125	4	App\\Models\\IdeeProjet	173	2025-07-24 10:43:47	2025-07-24 10:43:47	\N
127	4	App\\Models\\IdeeProjet	175	2025-07-24 10:56:24	2025-07-24 10:56:24	\N
29	4	App\\Models\\IdeeProjet	46	2025-07-24 04:52:30	2025-07-24 04:52:30	\N
30	4	App\\Models\\IdeeProjet	48	2025-07-24 04:54:50	2025-07-24 04:54:50	\N
31	4	App\\Models\\IdeeProjet	49	2025-07-24 05:12:13	2025-07-24 05:12:13	\N
129	4	App\\Models\\IdeeProjet	176	2025-07-24 11:22:12	2025-07-24 11:22:12	\N
131	4	App\\Models\\IdeeProjet	8	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
34	4	App\\Models\\IdeeProjet	52	2025-07-24 05:28:14	2025-07-24 05:28:14	\N
35	4	App\\Models\\IdeeProjet	53	2025-07-24 05:54:42	2025-07-24 05:54:42	\N
36	4	App\\Models\\IdeeProjet	54	2025-07-24 06:05:03	2025-07-24 06:05:03	\N
37	4	App\\Models\\IdeeProjet	55	2025-07-24 06:19:59	2025-07-24 06:19:59	\N
38	4	App\\Models\\IdeeProjet	56	2025-07-24 06:39:12	2025-07-24 06:39:12	\N
39	4	App\\Models\\IdeeProjet	57	2025-07-24 06:40:45	2025-07-24 06:40:45	\N
42	4	App\\Models\\IdeeProjet	65	2025-07-24 08:05:09	2025-07-24 08:05:09	\N
44	4	App\\Models\\IdeeProjet	68	2025-07-24 08:11:40	2025-07-24 08:11:40	\N
45	4	App\\Models\\IdeeProjet	70	2025-07-24 08:12:30	2025-07-24 08:12:30	\N
47	4	App\\Models\\IdeeProjet	72	2025-07-24 08:12:45	2025-07-24 08:12:45	\N
50	4	App\\Models\\IdeeProjet	75	2025-07-24 08:13:53	2025-07-24 08:13:53	\N
51	4	App\\Models\\IdeeProjet	76	2025-07-24 08:14:12	2025-07-24 08:14:12	\N
52	4	App\\Models\\IdeeProjet	82	2025-07-24 08:28:24	2025-07-24 08:28:24	\N
53	4	App\\Models\\IdeeProjet	88	2025-07-24 08:32:12	2025-07-24 08:32:12	\N
54	4	App\\Models\\IdeeProjet	89	2025-07-24 08:33:27	2025-07-24 08:33:27	\N
55	4	App\\Models\\IdeeProjet	91	2025-07-24 08:34:15	2025-07-24 08:34:15	\N
56	4	App\\Models\\IdeeProjet	92	2025-07-24 08:36:09	2025-07-24 08:36:09	\N
57	4	App\\Models\\IdeeProjet	93	2025-07-24 08:39:19	2025-07-24 08:39:19	\N
58	5	App\\Models\\IdeeProjet	94	2025-07-24 08:42:19	2025-07-24 08:42:19	\N
59	4	App\\Models\\IdeeProjet	95	2025-07-24 08:53:46	2025-07-24 08:53:46	\N
60	4	App\\Models\\IdeeProjet	96	2025-07-24 08:55:15	2025-07-24 08:55:15	\N
61	4	App\\Models\\IdeeProjet	97	2025-07-24 08:55:29	2025-07-24 08:55:29	\N
135	5	App\\Models\\IdeeProjet	16	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
136	5	App\\Models\\IdeeProjet	19	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
138	5	App\\Models\\IdeeProjet	21	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
142	5	App\\Models\\IdeeProjet	31	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
144	5	App\\Models\\IdeeProjet	33	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
146	4	App\\Models\\IdeeProjet	35	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
148	5	App\\Models\\IdeeProjet	37	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
150	5	App\\Models\\IdeeProjet	39	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
152	5	App\\Models\\IdeeProjet	42	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
154	5	App\\Models\\IdeeProjet	44	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
156	5	App\\Models\\IdeeProjet	62	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
124	4	App\\Models\\IdeeProjet	172	2025-07-24 10:42:51	2025-07-24 10:42:51	\N
126	4	App\\Models\\IdeeProjet	174	2025-07-24 10:55:38	2025-07-24 10:55:38	\N
130	4	App\\Models\\IdeeProjet	7	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
134	5	App\\Models\\IdeeProjet	13	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
137	5	App\\Models\\IdeeProjet	20	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
99	4	App\\Models\\IdeeProjet	135	2025-07-24 09:28:55	2025-07-24 09:28:55	\N
100	4	App\\Models\\IdeeProjet	136	2025-07-24 09:30:40	2025-07-24 09:30:40	\N
103	4	App\\Models\\IdeeProjet	139	2025-07-24 09:36:25	2025-07-24 09:36:25	\N
104	4	App\\Models\\IdeeProjet	140	2025-07-24 09:36:40	2025-07-24 09:36:40	\N
105	4	App\\Models\\IdeeProjet	141	2025-07-24 09:37:49	2025-07-24 09:37:49	\N
106	4	App\\Models\\IdeeProjet	144	2025-07-24 09:42:42	2025-07-24 09:42:42	\N
107	4	App\\Models\\IdeeProjet	145	2025-07-24 09:43:05	2025-07-24 09:43:05	\N
108	4	App\\Models\\IdeeProjet	146	2025-07-24 09:44:19	2025-07-24 09:44:19	\N
109	4	App\\Models\\IdeeProjet	147	2025-07-24 09:46:02	2025-07-24 09:46:02	\N
110	4	App\\Models\\IdeeProjet	148	2025-07-24 09:46:55	2025-07-24 09:46:55	\N
143	5	App\\Models\\IdeeProjet	32	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
145	5	App\\Models\\IdeeProjet	34	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
147	5	App\\Models\\IdeeProjet	36	2025-07-25 16:51:49	2025-07-25 16:51:49	\N
149	5	App\\Models\\IdeeProjet	38	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
151	5	App\\Models\\IdeeProjet	40	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
153	5	App\\Models\\IdeeProjet	43	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
155	5	App\\Models\\IdeeProjet	45	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
159	5	App\\Models\\IdeeProjet	73	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
\.


--
-- Data for Name: commentaires; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.commentaires (id, commentaire, date, commentaireable_type, commentaireable_id, "commentateurId", created_at, updated_at, deleted_at) FROM stdin;
\.


--
-- Data for Name: communes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.communes (id, code, nom, slug, "departementId", created_at, updated_at, deleted_at) FROM stdin;
1	AL-BAN	Banikoara	banikoara	1	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
2	AL-GOG	Gogounou	gogounou	1	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
3	AL-KAN	Kandi	kandi	1	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
4	AL-KAR	Karimama	karimama	1	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
5	AL-MAL	Malanville	malanville	1	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
6	AL-SEG	Segbana	segbana	1	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
7	AK-BOU	Boukoumbé	boukoumbe	2	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
8	AK-COB	Cobly	cobly	2	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
9	AK-KER	Kérou	kerou	2	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
10	AK-KOU	Kouandé	kouande	2	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
11	AK-MAT	Matéri	materi	2	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
12	AK-NAT	Natitingou	natitingou	2	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
13	AK-PEH	Péhunco	pehunco	2	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
14	AK-TAN	Tanguiéta	tanguieta	2	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
15	AK-TOU	Toucountouna	toucountouna	2	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
16	AT-ABC	Abomey-Calavi	abomey-calavi	3	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
17	AT-ALL	Allada	allada	3	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
18	AT-KPO	Kpomassè	kpomasse	3	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
19	AT-OUI	Ouidah	ouidah	3	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
20	AT-SAV	Sô-Ava	so-ava	3	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
21	AT-TOF	Toffo	toffo	3	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
22	AT-TOR	Tori-Bossito	tori-bossito	3	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
23	AT-ZE	Zè	ze	3	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
24	BO-BEM	Bembéréké	bembereke	4	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
25	BO-KAL	Kalalé	kalale	4	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
26	BO-NDA	N'Dali	ndali	4	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
27	BO-NIK	Nikki	nikki	4	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
28	BO-PAR	Parakou	parakou	4	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
29	BO-PER	Pèrèrè	perere	4	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
30	BO-SIN	Sinendé	sinende	4	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
31	BO-TCH	Tchaourou	tchaourou	4	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
32	CO-BAN	Bantè	bante	5	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
33	CO-DAS	Dassa-Zoumé	dassa-zoume	5	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
34	CO-GLA	Glazoué	glazoue	5	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
35	CO-OUE	Ouèssè	ouesse	5	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
36	CO-SAV	Savalou	savalou	5	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
37	CO-SAV2	Savè	save	5	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
38	KO-APL	Aplahoué	aplahoue	7	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
39	KO-DJA	Djakotomey	djakotomey	7	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
40	KO-DOG	Dogbo	dogbo	7	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
41	KO-KLO	Klouékanmè	klouekanme	7	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
42	KO-LAL	Lalo	lalo	7	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
43	KO-TOV	Toviklin	toviklin	7	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
44	DO-BAS	Bassila	bassila	6	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
45	DO-COP	Copargo	copargo	6	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
46	DO-DJO	Djougou	djougou	6	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
47	DO-OUA	Ouaké	ouake	6	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
48	LI-COT	Cotonou	cotonou	8	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
49	MO-ATH	Athiémé	atheme	9	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
50	MO-BOP	Bopa	bopa	9	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
51	MO-COM	Comè	come	9	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
52	MO-GPO	Grand-Popo	grand-popo	9	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
53	MO-HOU	Houéyogbé	houeyogbe	9	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
54	MO-LOK	Lokossa	lokossa	9	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
55	OU-ADJ	Adjarra	adjarra	10	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
56	OU-ADH	Adjohoun	adjohoun	10	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
57	OU-AGU	Aguégués	aguegues	10	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
58	OU-AKM	Akpro-Missérété	akpro-misserete	10	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
59	OU-AVR	Avrankou	avrankou	10	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
60	OU-BON	Bonou	bonou	10	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
61	OU-DAN	Dangbo	dangbo	10	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
62	OU-PNV	Porto-Novo	porto-novo	10	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
63	OU-SKP	Sèmè-Kpodji	seme-kpodji	10	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
64	PL-AOU	Adja-Ouèrè	adja-ouere	11	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
65	PL-IFA	Ifangni	ifangni	11	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
66	PL-KET	Kétou	ketou	11	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
67	PL-POB	Pobè	pobe	11	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
68	PL-SAK	Sakété	sakete	11	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
69	ZO-ABO	Abomey	abomey	12	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
70	ZO-AGB	Agbangnizoun	agbangnizoun	12	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
71	ZO-BOH	Bohicon	bohicon	12	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
72	ZO-COV	Covè	cove	12	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
73	ZO-DJI	Djidja	djidja	12	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
74	ZO-OUIN	Ouinhi	ouinhi	12	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
75	ZO-ZKP	Za-Kpota	za-kpota	12	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
76	ZO-ZAG	Zagnanado	zagnanado	12	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
77	ZO-ZOG	Zogbodomey	zogbodomey	12	2025-07-28 15:34:00	2025-07-28 15:34:00	\N
\.


--
-- Data for Name: composants_programme; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.composants_programme (id, indice, intitule, slug, "typeId", created_at, updated_at, deleted_at) FROM stdin;
1	1	1753080902::Orientation du pnd	1753080902::orientation-du-pnd	4	2025-07-18 12:32:52	2025-07-21 06:55:02	2025-07-21 06:55:02
2	1	1753080955::Objectifs du pnd	1753080955::objectifs-du-pnd	4	2025-07-21 06:53:14	2025-07-21 06:55:55	2025-07-21 06:55:55
3	1	Objectifs du pnd	objectifs-du-pnd	4	2025-07-23 14:31:01	2025-07-23 14:31:01	\N
4	1	Objectifs stratégique du pnd	objectifs-stratégique-du-pnd	9	2025-07-23 14:31:31	2025-07-23 14:31:31	\N
5	1	Pilier de pag	pilier-de-pag	1	2025-07-23 19:21:00	2025-07-23 19:21:00	\N
6	1	Piliers de pag	piliers-de-pag	5	2025-07-23 19:22:12	2025-07-23 19:22:12	\N
7	1	Piliers du pag	piliers-du-pag	6	2025-07-23 19:23:04	2025-07-23 19:23:04	\N
8	1	L\\'Orientation stratégique	l'orientation-stratégique	7	2025-07-23 20:00:01	2025-07-23 20:00:01	\N
10	1	L\\'action du pag	l'action-du-pag	13	2025-07-23 20:03:21	2025-07-23 20:03:21	\N
13	3	1753317363::Oiu	1753317363::oiu	12	2025-07-24 00:35:48	2025-07-24 00:36:03	2025-07-24 00:36:03
9	1	1753317371::L \\'Objectif stratégique	1753317371::l'objectif-stratégique	8	2025-07-23 20:00:34	2025-07-24 00:36:11	2025-07-24 00:36:11
12	6	1753317376::Iuuuuy	1753317376::iuuuuy	12	2025-07-24 00:35:09	2025-07-24 00:36:16	2025-07-24 00:36:16
11	3	1753317382::Jkjh	1753317382::jkjh	12	2025-07-24 00:33:31	2025-07-24 00:36:22	2025-07-24 00:36:22
14	1	Objectifhs stratégique du pnd	objectifhs-stratégique-du-pnd	8	2025-07-24 04:05:18	2025-07-24 04:05:18	\N
15	4	1753363073::Ioituor	1753363073::ioituor	6	2025-07-24 13:17:39	2025-07-24 13:17:53	2025-07-24 13:17:53
16	4	hhh	hhh	14	2025-07-25 11:30:14	2025-07-25 11:30:14	\N
\.


--
-- Data for Name: composants_projet; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.composants_projet (id, "composantId", projetable_type, projetable_id, created_at, updated_at, deleted_at) FROM stdin;
7	8	App\\Models\\IdeeProjet	41	2025-07-24 04:27:19	2025-07-24 04:27:19	\N
8	4	App\\Models\\IdeeProjet	41	2025-07-24 04:27:19	2025-07-24 04:27:19	\N
9	9	App\\Models\\IdeeProjet	41	2025-07-24 04:27:19	2025-07-24 04:27:19	\N
10	8	App\\Models\\IdeeProjet	42	2025-07-24 04:30:05	2025-07-24 04:30:05	\N
11	4	App\\Models\\IdeeProjet	42	2025-07-24 04:30:05	2025-07-24 04:30:05	\N
13	8	App\\Models\\IdeeProjet	43	2025-07-24 04:33:45	2025-07-24 04:33:45	\N
14	4	App\\Models\\IdeeProjet	43	2025-07-24 04:33:45	2025-07-24 04:33:45	\N
16	8	App\\Models\\IdeeProjet	44	2025-07-24 04:48:42	2025-07-24 04:48:42	\N
17	4	App\\Models\\IdeeProjet	44	2025-07-24 04:48:42	2025-07-24 04:48:42	\N
19	8	App\\Models\\IdeeProjet	45	2025-07-24 04:49:58	2025-07-24 04:49:58	\N
20	4	App\\Models\\IdeeProjet	45	2025-07-24 04:49:58	2025-07-24 04:49:58	\N
22	8	App\\Models\\IdeeProjet	46	2025-07-24 04:52:30	2025-07-24 04:52:30	\N
23	4	App\\Models\\IdeeProjet	46	2025-07-24 04:52:30	2025-07-24 04:52:30	\N
24	9	App\\Models\\IdeeProjet	46	2025-07-24 04:52:30	2025-07-24 04:52:30	\N
25	8	App\\Models\\IdeeProjet	48	2025-07-24 04:54:50	2025-07-24 04:54:50	\N
26	4	App\\Models\\IdeeProjet	48	2025-07-24 04:54:50	2025-07-24 04:54:50	\N
27	9	App\\Models\\IdeeProjet	48	2025-07-24 04:54:50	2025-07-24 04:54:50	\N
28	8	App\\Models\\IdeeProjet	49	2025-07-24 05:12:13	2025-07-24 05:12:13	\N
29	4	App\\Models\\IdeeProjet	49	2025-07-24 05:12:13	2025-07-24 05:12:13	\N
30	9	App\\Models\\IdeeProjet	49	2025-07-24 05:12:13	2025-07-24 05:12:13	\N
313	8	App\\Models\\IdeeProjet	174	2025-07-24 10:55:38	2025-07-24 10:55:38	\N
314	4	App\\Models\\IdeeProjet	174	2025-07-24 10:55:38	2025-07-24 10:55:38	\N
315	14	App\\Models\\IdeeProjet	174	2025-07-24 10:55:38	2025-07-24 10:55:38	\N
37	8	App\\Models\\IdeeProjet	52	2025-07-24 05:28:14	2025-07-24 05:28:14	\N
38	4	App\\Models\\IdeeProjet	52	2025-07-24 05:28:14	2025-07-24 05:28:14	\N
39	9	App\\Models\\IdeeProjet	52	2025-07-24 05:28:14	2025-07-24 05:28:14	\N
40	8	App\\Models\\IdeeProjet	53	2025-07-24 05:54:42	2025-07-24 05:54:42	\N
41	4	App\\Models\\IdeeProjet	53	2025-07-24 05:54:42	2025-07-24 05:54:42	\N
42	9	App\\Models\\IdeeProjet	53	2025-07-24 05:54:43	2025-07-24 05:54:43	\N
43	8	App\\Models\\IdeeProjet	54	2025-07-24 06:05:03	2025-07-24 06:05:03	\N
44	4	App\\Models\\IdeeProjet	54	2025-07-24 06:05:03	2025-07-24 06:05:03	\N
45	9	App\\Models\\IdeeProjet	54	2025-07-24 06:05:03	2025-07-24 06:05:03	\N
46	8	App\\Models\\IdeeProjet	55	2025-07-24 06:19:59	2025-07-24 06:19:59	\N
47	4	App\\Models\\IdeeProjet	55	2025-07-24 06:19:59	2025-07-24 06:19:59	\N
48	14	App\\Models\\IdeeProjet	55	2025-07-24 06:19:59	2025-07-24 06:19:59	\N
49	8	App\\Models\\IdeeProjet	56	2025-07-24 06:39:12	2025-07-24 06:39:12	\N
50	4	App\\Models\\IdeeProjet	56	2025-07-24 06:39:12	2025-07-24 06:39:12	\N
51	9	App\\Models\\IdeeProjet	56	2025-07-24 06:39:12	2025-07-24 06:39:12	\N
52	8	App\\Models\\IdeeProjet	57	2025-07-24 06:40:45	2025-07-24 06:40:45	\N
53	4	App\\Models\\IdeeProjet	57	2025-07-24 06:40:45	2025-07-24 06:40:45	\N
54	14	App\\Models\\IdeeProjet	57	2025-07-24 06:40:45	2025-07-24 06:40:45	\N
55	8	App\\Models\\IdeeProjet	58	2025-07-24 06:41:31	2025-07-24 06:41:31	\N
56	4	App\\Models\\IdeeProjet	58	2025-07-24 06:41:31	2025-07-24 06:41:31	\N
57	14	App\\Models\\IdeeProjet	58	2025-07-24 06:41:31	2025-07-24 06:41:31	\N
58	8	App\\Models\\IdeeProjet	63	2025-07-24 07:49:37	2025-07-24 07:49:37	\N
59	4	App\\Models\\IdeeProjet	63	2025-07-24 07:49:37	2025-07-24 07:49:37	\N
60	9	App\\Models\\IdeeProjet	63	2025-07-24 07:49:37	2025-07-24 07:49:37	\N
61	8	App\\Models\\IdeeProjet	65	2025-07-24 08:05:09	2025-07-24 08:05:09	\N
62	4	App\\Models\\IdeeProjet	65	2025-07-24 08:05:09	2025-07-24 08:05:09	\N
63	9	App\\Models\\IdeeProjet	65	2025-07-24 08:05:09	2025-07-24 08:05:09	\N
64	8	App\\Models\\IdeeProjet	67	2025-07-24 08:10:39	2025-07-24 08:10:39	\N
65	4	App\\Models\\IdeeProjet	67	2025-07-24 08:10:39	2025-07-24 08:10:39	\N
66	9	App\\Models\\IdeeProjet	67	2025-07-24 08:10:39	2025-07-24 08:10:39	\N
67	8	App\\Models\\IdeeProjet	68	2025-07-24 08:11:40	2025-07-24 08:11:40	\N
68	4	App\\Models\\IdeeProjet	68	2025-07-24 08:11:40	2025-07-24 08:11:40	\N
69	9	App\\Models\\IdeeProjet	68	2025-07-24 08:11:40	2025-07-24 08:11:40	\N
70	8	App\\Models\\IdeeProjet	70	2025-07-24 08:12:30	2025-07-24 08:12:30	\N
71	4	App\\Models\\IdeeProjet	70	2025-07-24 08:12:30	2025-07-24 08:12:30	\N
72	9	App\\Models\\IdeeProjet	70	2025-07-24 08:12:30	2025-07-24 08:12:30	\N
73	8	App\\Models\\IdeeProjet	71	2025-07-24 08:12:39	2025-07-24 08:12:39	\N
74	4	App\\Models\\IdeeProjet	71	2025-07-24 08:12:39	2025-07-24 08:12:39	\N
75	9	App\\Models\\IdeeProjet	71	2025-07-24 08:12:39	2025-07-24 08:12:39	\N
76	8	App\\Models\\IdeeProjet	72	2025-07-24 08:12:45	2025-07-24 08:12:45	\N
77	4	App\\Models\\IdeeProjet	72	2025-07-24 08:12:46	2025-07-24 08:12:46	\N
78	9	App\\Models\\IdeeProjet	72	2025-07-24 08:12:46	2025-07-24 08:12:46	\N
79	8	App\\Models\\IdeeProjet	73	2025-07-24 08:12:52	2025-07-24 08:12:52	\N
80	4	App\\Models\\IdeeProjet	73	2025-07-24 08:12:52	2025-07-24 08:12:52	\N
82	8	App\\Models\\IdeeProjet	74	2025-07-24 08:13:21	2025-07-24 08:13:21	\N
83	4	App\\Models\\IdeeProjet	74	2025-07-24 08:13:21	2025-07-24 08:13:21	\N
85	8	App\\Models\\IdeeProjet	75	2025-07-24 08:13:53	2025-07-24 08:13:53	\N
86	4	App\\Models\\IdeeProjet	75	2025-07-24 08:13:53	2025-07-24 08:13:53	\N
87	9	App\\Models\\IdeeProjet	75	2025-07-24 08:13:53	2025-07-24 08:13:53	\N
88	8	App\\Models\\IdeeProjet	76	2025-07-24 08:14:12	2025-07-24 08:14:12	\N
89	4	App\\Models\\IdeeProjet	76	2025-07-24 08:14:12	2025-07-24 08:14:12	\N
90	9	App\\Models\\IdeeProjet	76	2025-07-24 08:14:12	2025-07-24 08:14:12	\N
91	8	App\\Models\\IdeeProjet	82	2025-07-24 08:28:24	2025-07-24 08:28:24	\N
92	4	App\\Models\\IdeeProjet	82	2025-07-24 08:28:24	2025-07-24 08:28:24	\N
93	9	App\\Models\\IdeeProjet	82	2025-07-24 08:28:24	2025-07-24 08:28:24	\N
94	8	App\\Models\\IdeeProjet	88	2025-07-24 08:32:12	2025-07-24 08:32:12	\N
95	4	App\\Models\\IdeeProjet	88	2025-07-24 08:32:12	2025-07-24 08:32:12	\N
96	9	App\\Models\\IdeeProjet	88	2025-07-24 08:32:12	2025-07-24 08:32:12	\N
97	8	App\\Models\\IdeeProjet	89	2025-07-24 08:33:27	2025-07-24 08:33:27	\N
98	4	App\\Models\\IdeeProjet	89	2025-07-24 08:33:27	2025-07-24 08:33:27	\N
99	9	App\\Models\\IdeeProjet	89	2025-07-24 08:33:27	2025-07-24 08:33:27	\N
100	8	App\\Models\\IdeeProjet	91	2025-07-24 08:34:15	2025-07-24 08:34:15	\N
101	4	App\\Models\\IdeeProjet	91	2025-07-24 08:34:15	2025-07-24 08:34:15	\N
102	14	App\\Models\\IdeeProjet	91	2025-07-24 08:34:15	2025-07-24 08:34:15	\N
103	8	App\\Models\\IdeeProjet	92	2025-07-24 08:36:09	2025-07-24 08:36:09	\N
104	4	App\\Models\\IdeeProjet	92	2025-07-24 08:36:09	2025-07-24 08:36:09	\N
105	14	App\\Models\\IdeeProjet	92	2025-07-24 08:36:09	2025-07-24 08:36:09	\N
106	8	App\\Models\\IdeeProjet	93	2025-07-24 08:39:19	2025-07-24 08:39:19	\N
107	4	App\\Models\\IdeeProjet	93	2025-07-24 08:39:19	2025-07-24 08:39:19	\N
108	14	App\\Models\\IdeeProjet	93	2025-07-24 08:39:19	2025-07-24 08:39:19	\N
109	8	App\\Models\\IdeeProjet	94	2025-07-24 08:42:19	2025-07-24 08:42:19	\N
110	4	App\\Models\\IdeeProjet	94	2025-07-24 08:42:19	2025-07-24 08:42:19	\N
111	14	App\\Models\\IdeeProjet	94	2025-07-24 08:42:19	2025-07-24 08:42:19	\N
112	8	App\\Models\\IdeeProjet	95	2025-07-24 08:53:46	2025-07-24 08:53:46	\N
113	4	App\\Models\\IdeeProjet	95	2025-07-24 08:53:46	2025-07-24 08:53:46	\N
114	14	App\\Models\\IdeeProjet	95	2025-07-24 08:53:46	2025-07-24 08:53:46	\N
115	8	App\\Models\\IdeeProjet	96	2025-07-24 08:55:15	2025-07-24 08:55:15	\N
116	4	App\\Models\\IdeeProjet	96	2025-07-24 08:55:15	2025-07-24 08:55:15	\N
117	14	App\\Models\\IdeeProjet	96	2025-07-24 08:55:15	2025-07-24 08:55:15	\N
118	8	App\\Models\\IdeeProjet	97	2025-07-24 08:55:29	2025-07-24 08:55:29	\N
119	4	App\\Models\\IdeeProjet	97	2025-07-24 08:55:29	2025-07-24 08:55:29	\N
120	14	App\\Models\\IdeeProjet	97	2025-07-24 08:55:29	2025-07-24 08:55:29	\N
307	8	App\\Models\\IdeeProjet	172	2025-07-24 10:42:51	2025-07-24 10:42:51	\N
308	4	App\\Models\\IdeeProjet	172	2025-07-24 10:42:51	2025-07-24 10:42:51	\N
309	14	App\\Models\\IdeeProjet	172	2025-07-24 10:42:51	2025-07-24 10:42:51	\N
316	8	App\\Models\\IdeeProjet	175	2025-07-24 10:56:24	2025-07-24 10:56:24	\N
317	4	App\\Models\\IdeeProjet	175	2025-07-24 10:56:24	2025-07-24 10:56:24	\N
318	14	App\\Models\\IdeeProjet	175	2025-07-24 10:56:24	2025-07-24 10:56:24	\N
322	8	App\\Models\\IdeeProjet	7	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
323	4	App\\Models\\IdeeProjet	7	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
324	14	App\\Models\\IdeeProjet	7	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
328	8	App\\Models\\IdeeProjet	13	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
329	4	App\\Models\\IdeeProjet	13	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
330	14	App\\Models\\IdeeProjet	13	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
334	8	App\\Models\\IdeeProjet	16	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
335	4	App\\Models\\IdeeProjet	16	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
336	14	App\\Models\\IdeeProjet	16	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
337	8	App\\Models\\IdeeProjet	17	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
338	4	App\\Models\\IdeeProjet	17	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
339	14	App\\Models\\IdeeProjet	17	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
343	8	App\\Models\\IdeeProjet	20	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
344	4	App\\Models\\IdeeProjet	20	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
345	14	App\\Models\\IdeeProjet	20	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
349	8	App\\Models\\IdeeProjet	26	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
350	4	App\\Models\\IdeeProjet	26	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
351	14	App\\Models\\IdeeProjet	26	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
355	8	App\\Models\\IdeeProjet	31	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
356	4	App\\Models\\IdeeProjet	31	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
357	14	App\\Models\\IdeeProjet	31	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
361	8	App\\Models\\IdeeProjet	33	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
362	4	App\\Models\\IdeeProjet	33	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
363	14	App\\Models\\IdeeProjet	33	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
367	8	App\\Models\\IdeeProjet	35	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
368	4	App\\Models\\IdeeProjet	35	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
369	14	App\\Models\\IdeeProjet	35	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
232	8	App\\Models\\IdeeProjet	135	2025-07-24 09:28:55	2025-07-24 09:28:55	\N
233	4	App\\Models\\IdeeProjet	135	2025-07-24 09:28:55	2025-07-24 09:28:55	\N
234	14	App\\Models\\IdeeProjet	135	2025-07-24 09:28:55	2025-07-24 09:28:55	\N
235	8	App\\Models\\IdeeProjet	136	2025-07-24 09:30:40	2025-07-24 09:30:40	\N
236	4	App\\Models\\IdeeProjet	136	2025-07-24 09:30:40	2025-07-24 09:30:40	\N
237	14	App\\Models\\IdeeProjet	136	2025-07-24 09:30:40	2025-07-24 09:30:40	\N
373	8	App\\Models\\IdeeProjet	37	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
374	4	App\\Models\\IdeeProjet	37	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
375	14	App\\Models\\IdeeProjet	37	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
379	8	App\\Models\\IdeeProjet	39	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
380	4	App\\Models\\IdeeProjet	39	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
381	14	App\\Models\\IdeeProjet	39	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
244	8	App\\Models\\IdeeProjet	139	2025-07-24 09:36:25	2025-07-24 09:36:25	\N
245	4	App\\Models\\IdeeProjet	139	2025-07-24 09:36:25	2025-07-24 09:36:25	\N
246	14	App\\Models\\IdeeProjet	139	2025-07-24 09:36:25	2025-07-24 09:36:25	\N
247	8	App\\Models\\IdeeProjet	140	2025-07-24 09:36:40	2025-07-24 09:36:40	\N
248	4	App\\Models\\IdeeProjet	140	2025-07-24 09:36:40	2025-07-24 09:36:40	\N
249	14	App\\Models\\IdeeProjet	140	2025-07-24 09:36:40	2025-07-24 09:36:40	\N
250	8	App\\Models\\IdeeProjet	141	2025-07-24 09:37:49	2025-07-24 09:37:49	\N
251	4	App\\Models\\IdeeProjet	141	2025-07-24 09:37:49	2025-07-24 09:37:49	\N
252	14	App\\Models\\IdeeProjet	141	2025-07-24 09:37:49	2025-07-24 09:37:49	\N
253	8	App\\Models\\IdeeProjet	144	2025-07-24 09:42:42	2025-07-24 09:42:42	\N
254	4	App\\Models\\IdeeProjet	144	2025-07-24 09:42:42	2025-07-24 09:42:42	\N
255	14	App\\Models\\IdeeProjet	144	2025-07-24 09:42:42	2025-07-24 09:42:42	\N
256	8	App\\Models\\IdeeProjet	145	2025-07-24 09:43:05	2025-07-24 09:43:05	\N
257	4	App\\Models\\IdeeProjet	145	2025-07-24 09:43:05	2025-07-24 09:43:05	\N
258	14	App\\Models\\IdeeProjet	145	2025-07-24 09:43:05	2025-07-24 09:43:05	\N
259	8	App\\Models\\IdeeProjet	146	2025-07-24 09:44:19	2025-07-24 09:44:19	\N
260	4	App\\Models\\IdeeProjet	146	2025-07-24 09:44:19	2025-07-24 09:44:19	\N
261	14	App\\Models\\IdeeProjet	146	2025-07-24 09:44:19	2025-07-24 09:44:19	\N
262	8	App\\Models\\IdeeProjet	147	2025-07-24 09:46:02	2025-07-24 09:46:02	\N
263	4	App\\Models\\IdeeProjet	147	2025-07-24 09:46:02	2025-07-24 09:46:02	\N
264	14	App\\Models\\IdeeProjet	147	2025-07-24 09:46:02	2025-07-24 09:46:02	\N
265	8	App\\Models\\IdeeProjet	148	2025-07-24 09:46:55	2025-07-24 09:46:55	\N
266	4	App\\Models\\IdeeProjet	148	2025-07-24 09:46:55	2025-07-24 09:46:55	\N
267	14	App\\Models\\IdeeProjet	148	2025-07-24 09:46:55	2025-07-24 09:46:55	\N
310	8	App\\Models\\IdeeProjet	173	2025-07-24 10:43:47	2025-07-24 10:43:47	\N
311	4	App\\Models\\IdeeProjet	173	2025-07-24 10:43:47	2025-07-24 10:43:47	\N
312	14	App\\Models\\IdeeProjet	173	2025-07-24 10:43:47	2025-07-24 10:43:47	\N
319	8	App\\Models\\IdeeProjet	176	2025-07-24 11:00:39	2025-07-24 11:00:39	\N
320	4	App\\Models\\IdeeProjet	176	2025-07-24 11:00:39	2025-07-24 11:00:39	\N
321	14	App\\Models\\IdeeProjet	176	2025-07-24 11:00:39	2025-07-24 11:00:39	\N
325	8	App\\Models\\IdeeProjet	8	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
326	4	App\\Models\\IdeeProjet	8	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
327	14	App\\Models\\IdeeProjet	8	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
331	8	App\\Models\\IdeeProjet	15	2025-07-24 20:47:49	2025-07-24 20:47:49	\N
332	4	App\\Models\\IdeeProjet	15	2025-07-24 20:47:49	2025-07-24 20:47:49	\N
333	14	App\\Models\\IdeeProjet	15	2025-07-24 20:47:49	2025-07-24 20:47:49	\N
340	8	App\\Models\\IdeeProjet	19	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
341	4	App\\Models\\IdeeProjet	19	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
342	14	App\\Models\\IdeeProjet	19	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
346	8	App\\Models\\IdeeProjet	21	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
347	4	App\\Models\\IdeeProjet	21	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
348	14	App\\Models\\IdeeProjet	21	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
352	8	App\\Models\\IdeeProjet	27	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
353	4	App\\Models\\IdeeProjet	27	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
354	14	App\\Models\\IdeeProjet	27	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
358	8	App\\Models\\IdeeProjet	32	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
359	4	App\\Models\\IdeeProjet	32	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
360	14	App\\Models\\IdeeProjet	32	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
364	8	App\\Models\\IdeeProjet	34	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
365	4	App\\Models\\IdeeProjet	34	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
366	14	App\\Models\\IdeeProjet	34	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
370	8	App\\Models\\IdeeProjet	36	2025-07-25 16:51:49	2025-07-25 16:51:49	\N
371	4	App\\Models\\IdeeProjet	36	2025-07-25 16:51:49	2025-07-25 16:51:49	\N
372	14	App\\Models\\IdeeProjet	36	2025-07-25 16:51:49	2025-07-25 16:51:49	\N
376	8	App\\Models\\IdeeProjet	38	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
377	4	App\\Models\\IdeeProjet	38	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
378	14	App\\Models\\IdeeProjet	38	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
382	8	App\\Models\\IdeeProjet	40	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
383	4	App\\Models\\IdeeProjet	40	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
384	14	App\\Models\\IdeeProjet	40	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
385	14	App\\Models\\IdeeProjet	42	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
386	14	App\\Models\\IdeeProjet	43	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
387	14	App\\Models\\IdeeProjet	44	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
388	14	App\\Models\\IdeeProjet	45	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
389	8	App\\Models\\IdeeProjet	62	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
390	4	App\\Models\\IdeeProjet	62	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
391	14	App\\Models\\IdeeProjet	62	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
392	8	App\\Models\\IdeeProjet	66	2025-07-28 11:50:07	2025-07-28 11:50:07	\N
393	4	App\\Models\\IdeeProjet	66	2025-07-28 11:50:07	2025-07-28 11:50:07	\N
394	14	App\\Models\\IdeeProjet	66	2025-07-28 11:50:07	2025-07-28 11:50:07	\N
395	8	App\\Models\\IdeeProjet	69	2025-07-28 13:34:21	2025-07-28 13:34:21	\N
396	4	App\\Models\\IdeeProjet	69	2025-07-28 13:34:21	2025-07-28 13:34:21	\N
397	14	App\\Models\\IdeeProjet	69	2025-07-28 13:34:21	2025-07-28 13:34:21	\N
398	14	App\\Models\\IdeeProjet	73	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
399	14	App\\Models\\IdeeProjet	74	2025-07-29 09:01:15	2025-07-29 09:01:15	\N
\.


--
-- Data for Name: criteres; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.criteres (id, intitule, ponderation, commentaire, is_mandatory, est_general, categorie_critere_id, created_at, updated_at, deleted_at) FROM stdin;
1	Atténuation	5	La réduction des émissions de gaz à effet de serre résultant de la mise en œuvre du projet, avec la taxonomie de l'UE comme guide pour établir un seuil en cas d'ambiguïté concernant l'importance de la contribution d'un projet à la réduction des émissions de GES (par exemple, un projet d'électricité avec des émissions de cycle de vie >100 g CO2e/kWh doit être classé comme « Faible atténuation » même s'il prétend le contraire).	t	f	1	2025-07-29 09:01:59	2025-07-29 09:01:59	\N
2	Adaptation	5	Les pertes évitées directement ou indirectement attribuables à la mise en œuvre d'un projet, conférant ainsi une résilience dans les secteurs vulnérables au climat (par exemple, l'agriculture, l'eau, l'énergie, la santé, etc.).	t	f	1	2025-07-29 09:01:59	2025-07-29 09:01:59	\N
3	Contribution à l'objectif CDN	3	Les idées de projet étant générées au niveau du ministère sectoriel, il est important d'articuler dès le début le potentiel d'un projet à contribuer aux objectifs d'engagement définis dans les contributions déterminées au niveau national du Bénin.	t	f	1	2025-07-29 09:01:59	2025-07-29 09:01:59	\N
4	Changement transformationnel	3	Capacité du projet à provoquer un changement de paradigme avec des impacts et des externalités positives plus larges. Le cadre suivant du GCF (Fonds vert pour le climat) est utile pour évaluer le changement transformationnel.	t	f	1	2025-07-29 09:01:59	2025-07-29 09:01:59	\N
5	Délai	5	La réduction des émissions de gaz à effet de serre résultant de la mise en œuvre du projet, avec la taxonomie de l'UE comme guide pour établir un seuil en cas d'ambiguïté concernant l'importance de la contribution d'un projet à la réduction des émissions de GES (par exemple, un projet d'électricité avec des émissions de cycle de vie >100 g CO2e/kWh doit être classé comme « Faible atténuation » même s'il prétend le contraire).	t	f	7	2025-07-29 11:59:57	2025-07-29 11:59:57	\N
6	Adaptation	5	Les pertes évitées directement ou indirectement attribuables à la mise en œuvre d'un projet, conférant ainsi une résilience dans les secteurs vulnérables au climat (par exemple, l'agriculture, l'eau, l'énergie, la santé, etc.).	t	f	7	2025-07-29 11:59:57	2025-07-29 11:59:57	\N
7	Contribution à l'objectif CDN	3	Les idées de projet étant générées au niveau du ministère sectoriel, il est important d'articuler dès le début le potentiel d'un projet à contribuer aux objectifs d'engagement définis dans les contributions déterminées au niveau national du Bénin.	t	f	7	2025-07-29 11:59:57	2025-07-29 11:59:57	\N
\.


--
-- Data for Name: decisions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.decisions (id, valeur, date, observations, "observateurId", created_at, updated_at, deleted_at) FROM stdin;
\.


--
-- Data for Name: departements; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.departements (id, code, nom, slug, created_at, updated_at, deleted_at) FROM stdin;
1	AL	Alibori	alibori	2025-07-28 15:24:52	2025-07-28 15:24:52	\N
2	AK	Atacora	atacora	2025-07-28 15:24:52	2025-07-28 15:24:52	\N
3	AT	Atlantique	atlantique	2025-07-28 15:24:52	2025-07-28 15:24:52	\N
4	BO	Borgou	borgou	2025-07-28 15:24:52	2025-07-28 15:24:52	\N
5	CO	Collines	collines	2025-07-28 15:24:52	2025-07-28 15:24:52	\N
6	DO	Donga	donga	2025-07-28 15:24:52	2025-07-28 15:24:52	\N
7	KO	Kouffo	kouffo	2025-07-28 15:24:52	2025-07-28 15:24:52	\N
8	LI	Littoral	littoral	2025-07-28 15:24:52	2025-07-28 15:24:52	\N
9	MO	Mono	mono	2025-07-28 15:24:52	2025-07-28 15:24:52	\N
10	OU	Ouémé	oueme	2025-07-28 15:24:52	2025-07-28 15:24:52	\N
11	PL	Plateau	plateau	2025-07-28 15:24:52	2025-07-28 15:24:52	\N
12	ZO	Zou	zou	2025-07-28 15:24:52	2025-07-28 15:24:52	\N
\.


--
-- Data for Name: dgpd; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.dgpd (id, nom, slug, description, created_at, updated_at, deleted_at) FROM stdin;
2	Direction de la planification et de la finance	direction-de-la-planification-et-de-la-finance	\N	2025-07-29 10:20:45	2025-07-29 10:21:33	\N
\.


--
-- Data for Name: documents; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.documents (id, nom, slug, description, "categorieId", type, metadata, structure, created_at, updated_at, deleted_at) FROM stdin;
40	Fiche idée de projet	fiche-idée-de-projet	Formulaire de création d'une idée de projet	6	formulaire	\N	{"id": 40, "nom": "Fiche idée de projet", "type": "formulaire", "champs": {}, "metadata": null, "sections": [{"id": 38, "key": "informations-générales", "type": "formulaire", "champs": [{"id": 161, "key": "titre_projet", "info": "", "label": "Titre du projet", "attribut": "titre_projet", "sectionId": 38, "type_champ": "text", "isEvaluated": false, "is_required": true, "placeholder": "Saisissez le titre de votre projet", "meta_options": {"configs": {"max_length": 255, "min_length": 1}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 1, "startWithNewLine": null}, {"id": 162, "key": "sigle", "info": "", "label": "Sigle du projet", "attribut": "sigle", "sectionId": 38, "type_champ": "text", "isEvaluated": false, "is_required": false, "placeholder": "Acronyme du projet", "meta_options": {"configs": {"max_length": 50, "min_length": 1}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 2, "startWithNewLine": null}, {"id": 163, "key": "categorieId", "info": "", "label": "Categorie de projet", "attribut": "categorieId", "sectionId": 38, "type_champ": "select", "isEvaluated": false, "is_required": true, "placeholder": "Nom du ministère de rattachement", "meta_options": {"configs": {"max_length": 255, "min_length": 1}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 3, "startWithNewLine": null}, {"id": 165, "key": "duree", "info": "", "label": "Durée", "attribut": "duree", "sectionId": 38, "type_champ": "number", "isEvaluated": false, "is_required": true, "placeholder": "Ex: 24 mois", "meta_options": {"configs": {"max_length": 100, "min_length": 1}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 4, "startWithNewLine": null}, {"id": 166, "key": "cout_estimatif_projet", "info": "", "label": "Cout estimatig du projet", "attribut": "cout_estimatif_projet", "sectionId": 38, "type_champ": "number", "isEvaluated": false, "is_required": true, "placeholder": "0", "meta_options": {"configs": {"max": null, "min": 0, "step": 1}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": "0", "champ_standard": true, "ordre_affichage": 5, "startWithNewLine": null}, {"id": 167, "key": "cout_devise", "info": "", "label": "Devise", "attribut": "cout_devise", "sectionId": 38, "type_champ": "select", "isEvaluated": false, "is_required": true, "placeholder": "Sélectionnez une devise", "meta_options": {"configs": {"options": [{"label": "FCFA", "value": "FCFA"}, {"label": "USD", "value": "USD"}, {"label": "EUR", "value": "EUR"}]}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": "FCFA", "champ_standard": true, "ordre_affichage": 6, "startWithNewLine": null}, {"id": 168, "key": "cout_dollar_americain", "info": "", "label": "Cout en dollar americain", "attribut": "cout_dollar_americain", "sectionId": 38, "type_champ": "number", "isEvaluated": false, "is_required": true, "placeholder": "0", "meta_options": {"configs": {"max": null, "min": 0, "step": 1}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": "0", "champ_standard": true, "ordre_affichage": 5, "startWithNewLine": null}, {"id": 169, "key": "cout_euro", "info": "", "label": "Cout en euro", "attribut": "cout_euro", "sectionId": 38, "type_champ": "number", "isEvaluated": false, "is_required": true, "placeholder": "0", "meta_options": {"configs": {"max": null, "min": 0, "step": 1}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": "0", "champ_standard": true, "ordre_affichage": 5, "startWithNewLine": null}, {"id": 170, "key": "cout_dollar_canadien", "info": "", "label": "Cout en dollar canadien", "attribut": "cout_dollar_canadien", "sectionId": 38, "type_champ": "number", "isEvaluated": false, "is_required": true, "placeholder": "0", "meta_options": {"configs": {"max": null, "min": 0, "step": 1}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": "0", "champ_standard": true, "ordre_affichage": 5, "startWithNewLine": null}], "intitule": "Informations Générales", "ordre_affichage": 1}, {"id": 39, "key": "secteur-d------'activité-et-localisation", "type": "formulaire", "champs": [{"id": 176, "key": "arrondissements", "info": "", "label": "Arrondissement", "attribut": "arrondissements", "sectionId": 39, "type_champ": "select", "isEvaluated": false, "is_required": false, "placeholder": "Choisissez un arrondissement", "meta_options": {"configs": {"options": []}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 6, "startWithNewLine": null}, {"id": 177, "key": "villages", "info": "", "label": "villages", "attribut": "villages", "sectionId": 39, "type_champ": "select", "isEvaluated": false, "is_required": false, "placeholder": "Selectionnez les villages", "meta_options": {"configs": {"multiple": true, "max_length": 255, "min_length": 1}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 7, "startWithNewLine": null}, {"id": 171, "key": "grand_secteur", "info": "", "label": "Grand Secteur", "attribut": "grand_secteur", "sectionId": 39, "type_champ": "select", "isEvaluated": false, "is_required": true, "placeholder": "Choisissez un grand secteur", "meta_options": {"configs": {"options": []}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 1, "startWithNewLine": null}, {"id": 172, "key": "secteur", "info": "", "label": "Secteur", "attribut": "secteur", "sectionId": 39, "type_champ": "select", "isEvaluated": false, "is_required": true, "placeholder": "Choisissez un secteur", "meta_options": {"configs": {"options": []}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 2, "startWithNewLine": null}, {"id": 173, "key": "secteurId", "info": "", "label": "Sous-Secteur", "attribut": "secteurId", "sectionId": 39, "type_champ": "select", "isEvaluated": false, "is_required": false, "placeholder": "Choisissez un sous-secteur", "meta_options": {"configs": {"options": []}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 3, "startWithNewLine": null}, {"id": 174, "key": "departements", "info": "", "label": "Département", "attribut": "departements", "sectionId": 39, "type_champ": "select", "isEvaluated": false, "is_required": true, "placeholder": "Choisissez un département", "meta_options": {"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 4, "startWithNewLine": null}, {"id": 175, "key": "communes", "info": "", "label": "Commune", "attribut": "communes", "sectionId": 39, "type_champ": "select", "isEvaluated": false, "is_required": false, "placeholder": "Choisissez une commune", "meta_options": {"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 5, "startWithNewLine": null}], "intitule": "Secteur d       'activité et Localisation", "ordre_affichage": 2}, {"id": 40, "key": "cadres-stratégiques", "type": "formulaire", "champs": [{"id": 181, "key": "objectifs_strategiques", "info": "", "label": "Objectif stratégique", "attribut": "objectifs_strategiques", "sectionId": 40, "type_champ": "select", "isEvaluated": false, "is_required": false, "placeholder": "Choisissez un objectif", "meta_options": {"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 4, "startWithNewLine": null}, {"id": 182, "key": "resultats_strategiques", "info": "", "label": "Résultat stratégique", "attribut": "resultats_strategiques", "sectionId": 40, "type_champ": "select", "isEvaluated": false, "is_required": false, "placeholder": "Choisissez un résultat", "meta_options": {"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 5, "startWithNewLine": null}, {"id": 183, "key": "axes_pag", "info": "", "label": "Axes du pag", "attribut": "axes_pag", "sectionId": 40, "type_champ": "select", "isEvaluated": false, "is_required": false, "placeholder": "Choisissez les axes du pags", "meta_options": {"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 8, "startWithNewLine": null}, {"id": 184, "key": "actions_pag", "info": "", "label": "Actions du pag", "attribut": "actions_pag", "sectionId": 40, "type_champ": "select", "isEvaluated": false, "is_required": false, "placeholder": "Choisissez une action", "meta_options": {"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 9, "startWithNewLine": null}, {"id": 178, "key": "odds", "info": "", "label": "ODD", "attribut": "odds", "sectionId": 40, "type_champ": "select", "isEvaluated": false, "is_required": true, "placeholder": "Sélectionnez un ODD", "meta_options": {"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 1, "startWithNewLine": null}, {"id": 212, "key": "piliers_pag", "info": "", "label": "Piliers du pag", "attribut": "piliers_pag", "sectionId": 40, "type_champ": "select", "isEvaluated": false, "is_required": false, "placeholder": "Choisissez les piliers", "meta_options": {"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 7, "startWithNewLine": null}, {"id": 180, "key": "orientations_strategiques", "info": "", "label": "Orientation stratégique", "attribut": "orientations_strategiques", "sectionId": 40, "type_champ": "select", "isEvaluated": false, "is_required": true, "placeholder": "Choisissez une orientation", "meta_options": {"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 3, "startWithNewLine": null}, {"id": 179, "key": "cibles", "info": "", "label": "Cibles", "attribut": "cibles", "sectionId": 40, "type_champ": "select", "isEvaluated": false, "is_required": false, "placeholder": "Sélectionnez les cibles", "meta_options": {"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 2, "startWithNewLine": null}], "intitule": "Cadres stratégiques", "ordre_affichage": 3}, {"id": 41, "key": "financement-et-bénéficiaires", "type": "formulaire", "champs": [{"id": 187, "key": "sources_financement", "info": "", "label": "Source de financement", "attribut": "sources_financement", "sectionId": 41, "type_champ": "select", "isEvaluated": false, "is_required": false, "placeholder": "Choisissez une source", "meta_options": {"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 3, "startWithNewLine": null}, {"id": 188, "key": "public_cible", "info": "", "label": "Public cible", "attribut": "public_cible", "sectionId": 41, "type_champ": "textarea", "isEvaluated": false, "is_required": true, "placeholder": "Décrivez le public cible du projet", "meta_options": {"configs": {"max_length": 1000, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 4, "startWithNewLine": null}, {"id": 189, "key": "parties_prenantes", "info": "", "label": "Parties prenantes", "attribut": "parties_prenantes", "sectionId": 41, "type_champ": "textarea", "isEvaluated": false, "is_required": true, "placeholder": "Identifiez les parties prenantes impliquées", "meta_options": {"configs": {"max_length": 1000, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 6, "startWithNewLine": null}, {"id": 213, "key": "constats_majeurs", "info": "", "label": "Constats majeurs", "attribut": "constats_majeurs", "sectionId": 41, "type_champ": "textarea", "isEvaluated": false, "is_required": true, "placeholder": "", "meta_options": {"configs": {"max_length": 1000, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 5, "startWithNewLine": null}, {"id": 185, "key": "types_financement", "info": "", "label": "Types de financement", "attribut": "types_financement", "sectionId": 41, "type_champ": "select", "isEvaluated": false, "is_required": true, "placeholder": "Choisissez un type", "meta_options": {"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 1, "startWithNewLine": null}, {"id": 186, "key": "natures_financement", "info": "", "label": "Nature du financement", "attribut": "natures_financement", "sectionId": 41, "type_champ": "select", "isEvaluated": false, "is_required": false, "placeholder": "Choisissez une nature", "meta_options": {"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 2, "startWithNewLine": null}], "intitule": "Financement et Bénéficiaires", "ordre_affichage": 4}, {"id": 42, "key": "contexte-et-analyse", "type": "formulaire", "champs": [{"id": 192, "key": "resultats_attendus", "info": "", "label": "Résultats attendus", "attribut": "resultats_attendus", "sectionId": 42, "type_champ": "textarea", "isEvaluated": false, "is_required": true, "placeholder": "Décrivez les résultats attendus", "meta_options": {"configs": {"max_length": 2000, "min_length": 20}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 2, "startWithNewLine": null}, {"id": 193, "key": "origine", "info": "", "label": "Origine du projet", "attribut": "origine", "sectionId": 42, "type_champ": "textarea", "isEvaluated": false, "is_required": true, "placeholder": "D'où vient l'idée de ce projet ?", "meta_options": {"configs": {"max_length": 1500, "min_length": 20}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 3, "startWithNewLine": null}, {"id": 194, "key": "fondement", "info": "", "label": "Fondement du projet", "attribut": "fondement", "sectionId": 42, "type_champ": "textarea", "isEvaluated": false, "is_required": true, "placeholder": "Sur quoi se base ce projet ?", "meta_options": {"configs": {"max_length": 1500, "min_length": 20}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 4, "startWithNewLine": null}, {"id": 195, "key": "situation_actuelle", "info": "", "label": "Situation actuelle", "attribut": "situation_actuelle", "sectionId": 42, "type_champ": "textarea", "isEvaluated": false, "is_required": true, "placeholder": "Décrivez la situation actuelle", "meta_options": {"configs": {"max_length": 2000, "min_length": 20}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 5, "startWithNewLine": null}, {"id": 196, "key": "situation_desiree", "info": "", "label": "Situation désirée", "attribut": "situation_desiree", "sectionId": 42, "type_champ": "textarea", "isEvaluated": false, "is_required": true, "placeholder": "Décrivez la situation visée", "meta_options": {"configs": {"max_length": 2000, "min_length": 20}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 6, "startWithNewLine": null}, {"id": 197, "key": "contraintes", "info": "", "label": "Contraintes", "attribut": "contraintes", "sectionId": 42, "type_champ": "textarea", "isEvaluated": false, "is_required": false, "placeholder": "Identifiez les principales contraintes", "meta_options": {"configs": {"max_length": 1000, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 7, "startWithNewLine": null}, {"id": 190, "key": "objectif_general", "info": "", "label": "Objectif du projet", "attribut": "objectif_general", "sectionId": 42, "type_champ": "textarea", "isEvaluated": false, "is_required": true, "placeholder": "Décrivez l'objectif principal du projet", "meta_options": {"configs": {"max_length": 2000, "min_length": 20}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 1, "startWithNewLine": null}, {"id": 191, "key": "objectifs_specifiques", "info": "", "label": "Objectif Specifiques", "attribut": "objectifs_specifiques", "sectionId": 42, "type_champ": "textarea", "isEvaluated": false, "is_required": true, "placeholder": "Décrivez l'objectif principal du projet", "meta_options": {"configs": {"max_length": 2000, "min_length": 20}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 1, "startWithNewLine": null}], "intitule": "Contexte et Analyse", "ordre_affichage": 5}, {"id": 43, "key": "description-technique-et-impacts", "type": "formulaire", "champs": [{"id": 204, "key": "estimation_couts", "info": "", "label": "Estimation des couts et benefices", "attribut": "estimation_couts", "sectionId": 43, "type_champ": "textarea", "isEvaluated": false, "is_required": false, "placeholder": "", "meta_options": {"configs": {"max_length": 1500, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 4, "startWithNewLine": null}, {"id": 201, "key": "caracteristiques_techniques", "info": "", "label": "Caractéristiques techniques", "attribut": "caracteristiques_techniques", "sectionId": 43, "type_champ": "textarea", "isEvaluated": false, "is_required": false, "placeholder": "Caractéristiques techniques", "meta_options": {"configs": {"max_length": 2000, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 2, "startWithNewLine": null}, {"id": 200, "key": "echeancier", "info": "https://docs.google.com/document/d/1U9p3N557lwFIt-mkkg3KOF_VMZ6IwIpx/edit#heading=h.17dp8vu", "label": "Echeancier du projet", "attribut": "echeancier", "sectionId": 43, "type_champ": "textarea", "isEvaluated": false, "is_required": true, "placeholder": "Description détaillée du projet", "meta_options": {"configs": {"max_length": 3000, "min_length": 50}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 1, "startWithNewLine": null}, {"id": 205, "key": "risques_immediats", "info": "", "label": "Risques immédiats", "attribut": "risques_immediats", "sectionId": 43, "type_champ": "textarea", "isEvaluated": false, "is_required": false, "placeholder": "Risques identifiés", "meta_options": {"configs": {"max_length": 1500, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 5, "startWithNewLine": null}, {"id": 206, "key": "conclusions", "info": "", "label": "Conclusions", "attribut": "conclusions", "sectionId": 43, "type_champ": "textarea", "isEvaluated": false, "is_required": false, "placeholder": "Conclusions générales", "meta_options": {"configs": {"max_length": 1500, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 6, "startWithNewLine": null}, {"id": 207, "key": "description", "info": "", "label": "Autre solutions alternatives considere et non retenues", "attribut": "description", "sectionId": 43, "type_champ": "textarea", "isEvaluated": false, "is_required": false, "placeholder": "Autre solutions alternatives", "meta_options": {"configs": {"max_length": 1500, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 6, "startWithNewLine": null}, {"id": 208, "key": "sommaire", "info": "", "label": "Description sommaire", "attribut": "sommaire", "sectionId": 43, "type_champ": "textarea", "isEvaluated": false, "is_required": false, "placeholder": "Description sommaire", "meta_options": {"configs": {"max_length": 1500, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 6, "startWithNewLine": null}, {"id": 202, "key": "impact_environnement", "info": "", "label": "Impact environnemental", "attribut": "impact_environnement", "sectionId": 43, "type_champ": "textarea", "isEvaluated": false, "is_required": false, "placeholder": "Impact sur l'environnement", "meta_options": {"configs": {"max_length": 1500, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 3, "startWithNewLine": null}, {"id": 203, "key": "aspect_organisationnel", "info": "", "label": "Aspects organisationnels", "attribut": "aspect_organisationnel", "sectionId": 43, "type_champ": "textarea", "isEvaluated": false, "is_required": false, "placeholder": "", "meta_options": {"configs": {"max_length": 1500, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 4, "startWithNewLine": null}, {"id": 198, "key": "description_projet", "info": "", "label": "Description du projet", "attribut": "description_projet", "sectionId": 43, "type_champ": "textarea", "isEvaluated": false, "is_required": true, "placeholder": "Description détaillée du projet", "meta_options": {"configs": {"max_length": 3000, "min_length": 50}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 1, "startWithNewLine": null}, {"id": 199, "key": "description_extrants", "info": "", "label": "Description du projet", "attribut": "description_extrants", "sectionId": 43, "type_champ": "textarea", "isEvaluated": false, "is_required": true, "placeholder": "Description détaillée du projet", "meta_options": {"configs": {"max_length": 3000, "min_length": 50}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 1, "startWithNewLine": null}], "intitule": "Description technique et Impacts", "ordre_affichage": 6}, {"id": 44, "key": "responsabilités", "type": "formulaire", "champs": [], "intitule": "Responsabilités", "ordre_affichage": 7}], "categorie": {"id": 6, "nom": "Fiche d 'idée", "format": "document", "description": "Formulaire standard d'ideation de projet"}, "structure": {"id": 40, "nom": "Fiche idée de projet", "type": "formulaire", "champs": [], "metadata": null, "sections": [{"id": 38, "key": "informations-générales", "type": "formulaire", "champs": [{"id": 161, "key": "titre_projet", "info": "", "label": "Titre du projet", "attribut": "titre_projet", "sectionId": 38, "type_champ": "text", "isEvaluated": false, "is_required": true, "placeholder": "Saisissez le titre de votre projet", "meta_options": {"configs": {"max_length": 255, "min_length": 1}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 1, "startWithNewLine": null}, {"id": 162, "key": "sigle", "info": "", "label": "Sigle du projet", "attribut": "sigle", "sectionId": 38, "type_champ": "text", "isEvaluated": false, "is_required": false, "placeholder": "Acronyme du projet", "meta_options": {"configs": {"max_length": 50, "min_length": 1}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 2, "startWithNewLine": null}, {"id": 163, "key": "categorieId", "info": "", "label": "Categorie de projet", "attribut": "categorieId", "sectionId": 38, "type_champ": "select", "isEvaluated": false, "is_required": true, "placeholder": "Nom du ministère de rattachement", "meta_options": {"configs": {"max_length": 255, "min_length": 1}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 3, "startWithNewLine": null}, {"id": 165, "key": "duree", "info": "", "label": "Durée", "attribut": "duree", "sectionId": 38, "type_champ": "text", "isEvaluated": false, "is_required": true, "placeholder": "Ex: 24 mois", "meta_options": {"configs": {"max_length": 100, "min_length": 1}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 4, "startWithNewLine": null}, {"id": 166, "key": "cout_estimatif_projet", "info": "", "label": "Cout estimatig du projet", "attribut": "cout_estimatif_projet", "sectionId": 38, "type_champ": "number", "isEvaluated": false, "is_required": true, "placeholder": "0", "meta_options": {"configs": {"max": null, "min": 0, "step": 1}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": "0", "champ_standard": true, "ordre_affichage": 5, "startWithNewLine": null}, {"id": 167, "key": "cout_devise", "info": "", "label": "Devise", "attribut": "cout_devise", "sectionId": 38, "type_champ": "select", "isEvaluated": false, "is_required": true, "placeholder": "Sélectionnez une devise", "meta_options": {"configs": {"options": [{"label": "FCFA", "value": "FCFA"}, {"label": "USD", "value": "USD"}, {"label": "EUR", "value": "EUR"}]}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": "FCFA", "champ_standard": true, "ordre_affichage": 6, "startWithNewLine": null}, {"id": 168, "key": "cout_dollar_americain", "info": "", "label": "Cout en dollar americain", "attribut": "cout_dollar_americain", "sectionId": 38, "type_champ": "number", "isEvaluated": false, "is_required": true, "placeholder": "0", "meta_options": {"configs": {"max": null, "min": 0, "step": 1}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": "0", "champ_standard": true, "ordre_affichage": 5, "startWithNewLine": null}, {"id": 169, "key": "cout_euro", "info": "", "label": "Cout en euro", "attribut": "cout_euro", "sectionId": 38, "type_champ": "number", "isEvaluated": false, "is_required": true, "placeholder": "0", "meta_options": {"configs": {"max": null, "min": 0, "step": 1}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": "0", "champ_standard": true, "ordre_affichage": 5, "startWithNewLine": null}, {"id": 170, "key": "cout_dollar_canadien", "info": "", "label": "Cout en dollar canadien", "attribut": "cout_dollar_canadien", "sectionId": 38, "type_champ": "number", "isEvaluated": false, "is_required": true, "placeholder": "0", "meta_options": {"configs": {"max": null, "min": 0, "step": 1}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": "0", "champ_standard": true, "ordre_affichage": 5, "startWithNewLine": null}], "intitule": "Informations Générales", "ordre_affichage": 1}, {"id": 39, "key": "secteur-d------'activité-et-localisation", "type": "formulaire", "champs": [{"id": 176, "key": "arrondissements", "info": "", "label": "Arrondissement", "attribut": "arrondissements", "sectionId": 39, "type_champ": "select", "isEvaluated": false, "is_required": false, "placeholder": "Choisissez un arrondissement", "meta_options": {"configs": {"options": []}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 6, "startWithNewLine": null}, {"id": 177, "key": "villages", "info": "", "label": "villages", "attribut": "villages", "sectionId": 39, "type_champ": "select", "isEvaluated": false, "is_required": false, "placeholder": "Selectionnez les villages", "meta_options": {"configs": {"multiple": true, "max_length": 255, "min_length": 1}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 7, "startWithNewLine": null}, {"id": 171, "key": "grand_secteur", "info": "", "label": "Grand Secteur", "attribut": "grand_secteur", "sectionId": 39, "type_champ": "select", "isEvaluated": false, "is_required": true, "placeholder": "Choisissez un grand secteur", "meta_options": {"configs": {"options": []}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 1, "startWithNewLine": null}, {"id": 172, "key": "secteur", "info": "", "label": "Secteur", "attribut": "secteur", "sectionId": 39, "type_champ": "select", "isEvaluated": false, "is_required": true, "placeholder": "Choisissez un secteur", "meta_options": {"configs": {"options": []}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 2, "startWithNewLine": null}, {"id": 173, "key": "secteurId", "info": "", "label": "Sous-Secteur", "attribut": "secteurId", "sectionId": 39, "type_champ": "select", "isEvaluated": false, "is_required": false, "placeholder": "Choisissez un sous-secteur", "meta_options": {"configs": {"options": []}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 3, "startWithNewLine": null}, {"id": 174, "key": "departements", "info": "", "label": "Département", "attribut": "departements", "sectionId": 39, "type_champ": "select", "isEvaluated": false, "is_required": true, "placeholder": "Choisissez un département", "meta_options": {"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 4, "startWithNewLine": null}, {"id": 175, "key": "communes", "info": "", "label": "Commune", "attribut": "communes", "sectionId": 39, "type_champ": "select", "isEvaluated": false, "is_required": false, "placeholder": "Choisissez une commune", "meta_options": {"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 5, "startWithNewLine": null}], "intitule": "Secteur d       'activité et Localisation", "ordre_affichage": 2}, {"id": 40, "key": "cadres-stratégiques", "type": "formulaire", "champs": [{"id": 181, "key": "objectifs_strategiques", "info": "", "label": "Objectif stratégique", "attribut": "objectifs_strategiques", "sectionId": 40, "type_champ": "select", "isEvaluated": false, "is_required": false, "placeholder": "Choisissez un objectif", "meta_options": {"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 4, "startWithNewLine": null}, {"id": 182, "key": "resultats_strategiques", "info": "", "label": "Résultat stratégique", "attribut": "resultats_strategiques", "sectionId": 40, "type_champ": "select", "isEvaluated": false, "is_required": false, "placeholder": "Choisissez un résultat", "meta_options": {"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 5, "startWithNewLine": null}, {"id": 183, "key": "axes_pag", "info": "", "label": "Axes du pag", "attribut": "axes_pag", "sectionId": 40, "type_champ": "select", "isEvaluated": false, "is_required": false, "placeholder": "Choisissez les axes du pags", "meta_options": {"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 8, "startWithNewLine": null}, {"id": 184, "key": "actions_pag", "info": "", "label": "Actions du pag", "attribut": "actions_pag", "sectionId": 40, "type_champ": "select", "isEvaluated": false, "is_required": false, "placeholder": "Choisissez une action", "meta_options": {"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 9, "startWithNewLine": null}, {"id": 178, "key": "odds", "info": "", "label": "ODD", "attribut": "odds", "sectionId": 40, "type_champ": "select", "isEvaluated": false, "is_required": true, "placeholder": "Sélectionnez un ODD", "meta_options": {"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 1, "startWithNewLine": null}, {"id": 212, "key": "piliers_pag", "info": "", "label": "Piliers du pag", "attribut": "piliers_pag", "sectionId": 40, "type_champ": "select", "isEvaluated": false, "is_required": false, "placeholder": "Choisissez les piliers", "meta_options": {"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 7, "startWithNewLine": null}, {"id": 180, "key": "orientations_strategiques", "info": "", "label": "Orientation stratégique", "attribut": "orientations_strategiques", "sectionId": 40, "type_champ": "select", "isEvaluated": false, "is_required": true, "placeholder": "Choisissez une orientation", "meta_options": {"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 3, "startWithNewLine": null}, {"id": 179, "key": "cibles", "info": "", "label": "Cibles", "attribut": "cibles", "sectionId": 40, "type_champ": "select", "isEvaluated": false, "is_required": false, "placeholder": "Sélectionnez les cibles", "meta_options": {"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 2, "startWithNewLine": null}], "intitule": "Cadres stratégiques", "ordre_affichage": 3}, {"id": 41, "key": "financement-et-bénéficiaires", "type": "formulaire", "champs": [{"id": 187, "key": "sources_financement", "info": "", "label": "Source de financement", "attribut": "sources_financement", "sectionId": 41, "type_champ": "select", "isEvaluated": false, "is_required": false, "placeholder": "Choisissez une source", "meta_options": {"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 3, "startWithNewLine": null}, {"id": 188, "key": "public_cible", "info": "", "label": "Public cible", "attribut": "public_cible", "sectionId": 41, "type_champ": "textarea", "isEvaluated": false, "is_required": true, "placeholder": "Décrivez le public cible du projet", "meta_options": {"configs": {"max_length": 1000, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 4, "startWithNewLine": null}, {"id": 189, "key": "parties_prenantes", "info": "", "label": "Parties prenantes", "attribut": "parties_prenantes", "sectionId": 41, "type_champ": "textarea", "isEvaluated": false, "is_required": true, "placeholder": "Identifiez les parties prenantes impliquées", "meta_options": {"configs": {"max_length": 1000, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 6, "startWithNewLine": null}, {"id": 213, "key": "constats_majeurs", "info": "", "label": "Constats majeurs", "attribut": "constats_majeurs", "sectionId": 41, "type_champ": "textarea", "isEvaluated": false, "is_required": true, "placeholder": "", "meta_options": {"configs": {"max_length": 1000, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 5, "startWithNewLine": null}, {"id": 185, "key": "types_financement", "info": "", "label": "Types de financement", "attribut": "types_financement", "sectionId": 41, "type_champ": "select", "isEvaluated": false, "is_required": true, "placeholder": "Choisissez un type", "meta_options": {"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 1, "startWithNewLine": null}, {"id": 186, "key": "natures_financement", "info": "", "label": "Nature du financement", "attribut": "natures_financement", "sectionId": 41, "type_champ": "select", "isEvaluated": false, "is_required": false, "placeholder": "Choisissez une nature", "meta_options": {"configs": {"options": [], "multiple": true}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 2, "startWithNewLine": null}], "intitule": "Financement et Bénéficiaires", "ordre_affichage": 4}, {"id": 42, "key": "contexte-et-analyse", "type": "formulaire", "champs": [{"id": 192, "key": "resultats_attendus", "info": "", "label": "Résultats attendus", "attribut": "resultats_attendus", "sectionId": 42, "type_champ": "textarea", "isEvaluated": false, "is_required": true, "placeholder": "Décrivez les résultats attendus", "meta_options": {"configs": {"max_length": 2000, "min_length": 20}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 2, "startWithNewLine": null}, {"id": 193, "key": "origine", "info": "", "label": "Origine du projet", "attribut": "origine", "sectionId": 42, "type_champ": "textarea", "isEvaluated": false, "is_required": true, "placeholder": "D'où vient l'idée de ce projet ?", "meta_options": {"configs": {"max_length": 1500, "min_length": 20}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 3, "startWithNewLine": null}, {"id": 194, "key": "fondement", "info": "", "label": "Fondement du projet", "attribut": "fondement", "sectionId": 42, "type_champ": "textarea", "isEvaluated": false, "is_required": true, "placeholder": "Sur quoi se base ce projet ?", "meta_options": {"configs": {"max_length": 1500, "min_length": 20}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 4, "startWithNewLine": null}, {"id": 195, "key": "situation_actuelle", "info": "", "label": "Situation actuelle", "attribut": "situation_actuelle", "sectionId": 42, "type_champ": "textarea", "isEvaluated": false, "is_required": true, "placeholder": "Décrivez la situation actuelle", "meta_options": {"configs": {"max_length": 2000, "min_length": 20}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 5, "startWithNewLine": null}, {"id": 196, "key": "situation_desiree", "info": "", "label": "Situation désirée", "attribut": "situation_desiree", "sectionId": 42, "type_champ": "textarea", "isEvaluated": false, "is_required": true, "placeholder": "Décrivez la situation visée", "meta_options": {"configs": {"max_length": 2000, "min_length": 20}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 6, "startWithNewLine": null}, {"id": 197, "key": "contraintes", "info": "", "label": "Contraintes", "attribut": "contraintes", "sectionId": 42, "type_champ": "textarea", "isEvaluated": false, "is_required": false, "placeholder": "Identifiez les principales contraintes", "meta_options": {"configs": {"max_length": 1000, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 7, "startWithNewLine": null}, {"id": 190, "key": "objectif_general", "info": "", "label": "Objectif du projet", "attribut": "objectif_general", "sectionId": 42, "type_champ": "textarea", "isEvaluated": false, "is_required": true, "placeholder": "Décrivez l'objectif principal du projet", "meta_options": {"configs": {"max_length": 2000, "min_length": 20}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 1, "startWithNewLine": null}, {"id": 191, "key": "objectifs_specifiques", "info": "", "label": "Objectif Specifiques", "attribut": "objectifs_specifiques", "sectionId": 42, "type_champ": "textarea", "isEvaluated": false, "is_required": true, "placeholder": "Décrivez l'objectif principal du projet", "meta_options": {"configs": {"max_length": 2000, "min_length": 20}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 1, "startWithNewLine": null}], "intitule": "Contexte et Analyse", "ordre_affichage": 5}, {"id": 43, "key": "description-technique-et-impacts", "type": "formulaire", "champs": [{"id": 204, "key": "estimation_couts", "info": "", "label": "Estimation des couts et benefices", "attribut": "estimation_couts", "sectionId": 43, "type_champ": "textarea", "isEvaluated": false, "is_required": false, "placeholder": "", "meta_options": {"configs": {"max_length": 1500, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 4, "startWithNewLine": null}, {"id": 201, "key": "caracteristiques_techniques", "info": "", "label": "Caractéristiques techniques", "attribut": "caracteristiques_techniques", "sectionId": 43, "type_champ": "textarea", "isEvaluated": false, "is_required": false, "placeholder": "Caractéristiques techniques", "meta_options": {"configs": {"max_length": 2000, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 2, "startWithNewLine": null}, {"id": 200, "key": "echeancier", "info": "https://docs.google.com/document/d/1U9p3N557lwFIt-mkkg3KOF_VMZ6IwIpx/edit#heading=h.17dp8vu", "label": "Echeancier du projet", "attribut": "echeancier", "sectionId": 43, "type_champ": "textarea", "isEvaluated": false, "is_required": true, "placeholder": "Description détaillée du projet", "meta_options": {"configs": {"max_length": 3000, "min_length": 50}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 1, "startWithNewLine": null}, {"id": 205, "key": "risques_immediats", "info": "", "label": "Risques immédiats", "attribut": "risques_immediats", "sectionId": 43, "type_champ": "textarea", "isEvaluated": false, "is_required": false, "placeholder": "Risques identifiés", "meta_options": {"configs": {"max_length": 1500, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 5, "startWithNewLine": null}, {"id": 206, "key": "conclusions", "info": "", "label": "Conclusions", "attribut": "conclusions", "sectionId": 43, "type_champ": "textarea", "isEvaluated": false, "is_required": false, "placeholder": "Conclusions générales", "meta_options": {"configs": {"max_length": 1500, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 6, "startWithNewLine": null}, {"id": 207, "key": "description", "info": "", "label": "Autre solutions alternatives considere et non retenues", "attribut": "description", "sectionId": 43, "type_champ": "textarea", "isEvaluated": false, "is_required": false, "placeholder": "Autre solutions alternatives", "meta_options": {"configs": {"max_length": 1500, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 6, "startWithNewLine": null}, {"id": 208, "key": "sommaire", "info": "", "label": "Description sommaire", "attribut": "sommaire", "sectionId": 43, "type_champ": "textarea", "isEvaluated": false, "is_required": false, "placeholder": "Description sommaire", "meta_options": {"configs": {"max_length": 1500, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 6, "startWithNewLine": null}, {"id": 202, "key": "impact_environnement", "info": "", "label": "Impact environnemental", "attribut": "impact_environnement", "sectionId": 43, "type_champ": "textarea", "isEvaluated": false, "is_required": false, "placeholder": "Impact sur l'environnement", "meta_options": {"configs": {"max_length": 1500, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 3, "startWithNewLine": null}, {"id": 203, "key": "aspect_organisationnel", "info": "", "label": "Aspects organisationnels", "attribut": "aspect_organisationnel", "sectionId": 43, "type_champ": "textarea", "isEvaluated": false, "is_required": false, "placeholder": "", "meta_options": {"configs": {"max_length": 1500, "min_length": 10}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": false}}, "default_value": null, "champ_standard": true, "ordre_affichage": 4, "startWithNewLine": null}, {"id": 198, "key": "description_projet", "info": "", "label": "Description du projet", "attribut": "description_projet", "sectionId": 43, "type_champ": "textarea", "isEvaluated": false, "is_required": true, "placeholder": "Description détaillée du projet", "meta_options": {"configs": {"max_length": 3000, "min_length": 50}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 1, "startWithNewLine": null}, {"id": 199, "key": "description_extrants", "info": "", "label": "Description du projet", "attribut": "description_extrants", "sectionId": 43, "type_champ": "textarea", "isEvaluated": false, "is_required": true, "placeholder": "Description détaillée du projet", "meta_options": {"configs": {"max_length": 3000, "min_length": 50}, "conditions": {"disable": false, "visible": true, "conditions": []}, "validations_rules": {"required": true}}, "default_value": null, "champ_standard": true, "ordre_affichage": 1, "startWithNewLine": null}], "intitule": "Description technique et Impacts", "ordre_affichage": 6}, {"id": 44, "key": "responsabilités", "type": "formulaire", "champs": [], "intitule": "Responsabilités", "ordre_affichage": 7}], "categorie": {"id": 6, "nom": "Fiche d 'idée", "format": "document", "description": "Formulaire standard d'ideation de projet"}, "structure": null, "description": "Formulaire de création d'une idée de projet"}, "description": "Formulaire de création d'une idée de projet"}	2025-07-23 05:18:58	2025-07-24 05:55:09	\N
\.


--
-- Data for Name: dpaf; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.dpaf (id, nom, slug, description, created_at, updated_at, deleted_at) FROM stdin;
3	Direction de la planification et de la finance	direction-de-la-planification-et-de-la-finance	\N	2025-07-29 09:52:58	2025-07-29 09:52:58	\N
\.


--
-- Data for Name: evaluation_champs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.evaluation_champs (id, note, commentaires, date_note, "evaluationId", "champId", created_at, updated_at, deleted_at) FROM stdin;
\.


--
-- Data for Name: evaluation_criteres; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.evaluation_criteres (id, note, evaluateur_id, notation_id, critere_id, categorie_critere_id, created_at, updated_at, deleted_at) FROM stdin;
\.


--
-- Data for Name: evaluations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.evaluations (id, created_at, updated_at, deleted_at) FROM stdin;
\.


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- Data for Name: financements; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.financements (id, nom, nom_usuel, slug, type, "financementId", created_at, updated_at, deleted_at) FROM stdin;
3	Financiere fgdfg	Financement financiere module	financiere-fgdfg	type	\N	2025-07-21 18:35:21	2025-07-21 18:35:21	\N
4	Financiere	Financement financiere module	financiere	type	\N	2025-07-22 14:50:52	2025-07-22 14:50:52	\N
5	Financierement	Financement financiere module	financierement	nature	4	2025-07-22 14:53:52	2025-07-22 14:53:52	\N
6	Financierement id	Financement financiere module	financierement-id	nature	4	2025-07-22 14:54:22	2025-07-22 14:54:22	\N
7	Reprehenderit corrup	Est dolor est culpa	reprehenderit-corrup	nature	\N	2025-07-23 06:21:05	2025-07-23 06:21:05	\N
9	dffdf	fdfd	dffdf	source	\N	2025-07-23 11:29:43	2025-07-23 11:29:43	\N
8	1753270206::Sit sit aut volupt	Nemo voluptatibus eo jkh	1753270206::sit-sit-aut-volupt	nature	\N	2025-07-23 11:29:16	2025-07-23 11:30:06	2025-07-23 11:30:06
10	Corrupti assumenda 	Quia quasi ipsam ani	corrupti-assumenda-	source	\N	2025-07-23 11:48:28	2025-07-23 11:48:28	\N
11	1753362693::Reiofj	fffffd	1753362693::reiofj	nature	\N	2025-07-24 13:11:19	2025-07-24 13:11:33	2025-07-24 13:11:33
12	hghhh	hhhh	hghhh	nature	\N	2025-07-25 11:28:30	2025-07-25 11:28:30	\N
13	jhjhhhhhhhh	jjjjjjjjjjj	jhjhhhhhhhh	nature	\N	2025-07-25 12:27:19	2025-07-25 12:27:19	\N
14	gttt	5656	gttt	source	\N	2025-07-25 12:29:28	2025-07-25 12:29:28	\N
\.


--
-- Data for Name: groupe_utilisateur_roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.groupe_utilisateur_roles (id, "roleId", "groupeUtilisateurId", created_at, updated_at, deleted_at) FROM stdin;
1	9	1	2025-07-29 09:26:49	2025-07-29 09:26:49	\N
2	9	2	2025-07-29 10:27:39	2025-07-29 10:27:39	\N
3	9	3	2025-07-29 10:32:40	2025-07-29 10:32:40	\N
4	9	4	2025-07-29 10:48:44	2025-07-29 10:48:44	\N
7	12	5	2025-07-29 11:22:55	2025-07-29 11:22:55	\N
8	11	5	2025-07-29 11:23:07	2025-07-29 11:23:07	\N
9	12	6	2025-07-29 11:30:33	2025-07-29 11:30:33	\N
\.


--
-- Data for Name: groupe_utilisateur_users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.groupe_utilisateur_users (id, "userId", "groupeUtilisateurId", created_at, updated_at, deleted_at) FROM stdin;
1	21	5	2025-07-29 11:28:40	2025-07-29 11:28:40	\N
2	21	6	2025-07-29 11:31:30	2025-07-29 11:31:30	\N
\.


--
-- Data for Name: groupes_utilisateur; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.groupes_utilisateur (id, nom, slug, description, profilable_type, profilable_id, created_at, updated_at, deleted_at) FROM stdin;
1	1753781293::Cellule de planification	1753781293::cellule-de-planification	\N	\N	\N	2025-07-29 09:26:01	2025-07-29 09:28:13	2025-07-29 09:28:13
2	Cellule technique	cellule-technique	\N	\N	\N	2025-07-29 10:27:39	2025-07-29 10:27:39	\N
3	Comite Ministeriel	comite-ministeriel	\N	\N	\N	2025-07-29 10:32:40	2025-07-29 10:32:40	\N
4	Coordination ministérielle	coordination-ministérielle	\N	\N	\N	2025-07-29 10:48:44	2025-07-29 10:48:44	\N
5	Cellule de planification ministérielle	cellule-de-planification-ministérielle	\N	App\\Models\\Organisation	14	2025-07-29 11:18:19	2025-07-29 11:20:26	\N
6	Comite de validation	comite-de-validation	\N	App\\Models\\Organisation	14	2025-07-29 11:30:33	2025-07-29 11:30:33	\N
\.


--
-- Data for Name: idees_projet; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.idees_projet (id, "secteurId", "ministereId", "categorieId", "responsableId", "demandeurId", identifiant_bip, identifiant_sigfp, est_coherent, statut, phase, sous_phase, decision, titre_projet, sigle, type_projet, duree, origine, fondement, situation_actuelle, situation_desiree, contraintes, description_projet, echeancier, description_extrants, caracteristiques, impact_environnement, aspect_organisationnel, risques_immediats, conclusions, description, description_decision, estimation_couts, public_cible, constats_majeurs, objectif_general, sommaire, score_climatique, score_amc, cout_dollar_americain, cout_euro, cout_dollar_canadien, date_debut_etude, date_fin_etude, cout_estimatif_projet, "ficheIdee", parties_prenantes, objectifs_specifiques, resultats_attendus, body_projet, isdeleted, created_at, updated_at, deleted_at, est_soumise) FROM stdin;
8	28	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	onsequaturdre fhg rerum	Sunt gfhgdsfds ddsfsfddfh	simple	6	Ad sed est et voluptatibus natus possimus dolor sed sit eius dolores velit possimus velit dolore voluptate ut	Consequat Et temporibus exercitationem molestiae aliqua Qui	Maxime rerum est aliqua Nesciunt sit possimus minima qui et ullamco	Veniam iure molestias ab amet et tempor aut similique	Pariatur Fuga Omnis voluptas ipsum vel quos expedita harum maxime aut est	Consequat Sint rerum cumque nulla vel cillum	ae quis omnis consequatur aut magnam porro	que fugiat consectetur aliquid qui et labore	\N	Sit quisquam officiis omnis eos in quis quis	Quo consequatur nobis non blanditiis deleniti ut	tatis ea nihil occaecat obcaecati	periores nihil est aut qui ratione ad in	epudiandae consequat Atque in id blanditiis tempor	\N	r earum nihil qui dolores dolores provident velit	jhgggghh	ghj	Fugiat sint est vero deleniti sint voluptatibus officiis et mollit ut culpa temporibus expedita eu ad anim non	r quis officia libero	0.00	0.00	58.00	39.00	38.00	\N	\N	{"montant":77,"devise":"FCFA"}	[]	["jhghj"]	["Quis pariatur Voluptatem omnis ad qui"]	["Velit quis voluptate dolor assumenda"]	[]	f	2025-07-24 20:02:39	2025-07-24 20:15:08	2025-07-24 20:15:08	f
7	28	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	onsequaturd fhg rerum	Sunt gfhg ddsfsfddfh	simple	6	Ad sed est et voluptatibus natus possimus dolor sed sit eius dolores velit possimus velit dolore voluptate ut	Consequat Et temporibus exercitationem molestiae aliqua Qui	Maxime rerum est aliqua Nesciunt sit possimus minima qui et ullamco	Veniam iure molestias ab amet et tempor aut similique	Pariatur Fuga Omnis voluptas ipsum vel quos expedita harum maxime aut est	Consequat Sint rerum cumque nulla vel cillum	ae quis omnis consequatur aut magnam porro	que fugiat consectetur aliquid qui et labore	\N	Sit quisquam officiis omnis eos in quis quis	Quo consequatur nobis non blanditiis deleniti ut	tatis ea nihil occaecat obcaecati	periores nihil est aut qui ratione ad in	epudiandae consequat Atque in id blanditiis tempor	\N	r earum nihil qui dolores dolores provident velit	jhgggghh	ghj	Fugiat sint est vero deleniti sint voluptatibus officiis et mollit ut culpa temporibus expedita eu ad anim non	r quis officia libero	0.00	0.00	58.00	39.00	38.00	\N	\N	{"montant":77,"devise":"FCFA"}	[]	["jhghj"]	["Quis pariatur Voluptatem omnis ad qui"]	["Velit quis voluptate dolor assumenda"]	[]	f	2025-07-24 20:02:00	2025-07-24 20:15:12	2025-07-24 20:15:12	f
15	27	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	hic velit velit placeat aute molestias nisi in in eum nihil amet	elit incidunt s	simple	93	Sunt assumenda dolor voluptas nesciunt eligendi non et officiis	Nesciunt reiciendis hic deleniti consequatur ducimus nostrum aspernatur exercitationem vero deleniti est quia	Minima animi dicta error ad rerum sint aut quidem inventore labore perspiciatis dolor et excepteur animi unde	Tenetur asperiores architecto quas sapiente laborum commodo incididunt voluptatem	Et consequatur officia aut exercitation esse consequat Necessitatibus voluptatem	Quae mollitia libero exercitation neque nobis iste eum sit qui id quo unde molestiae fugit molestias pariatur Ullamco aut	Cumque beatae totam quis sunt libero optio	Deserunt ea animi consectetur voluptas eum	\N	Facere eaque ut omnis ullam tempora quia quisquam nisi nihil magni quibusdam	Nemo a unde do nostrud in rerum	Cupiditate libero occaecat aut eveniet molestias corporis ipsa accusamus placeat	Rerum obcaecati nihil temporibus et omnis culpa sapiente consequatur consequatur voluptas ipsum	Et quaerat consequuntur elit et distinctio Laboris debitis	\N	Corrupti deleniti quibusdam blanditiis est ullamco vel eu sed non ratione	iuhyg	uytgf	Sit nisi animi quaerat pariatur	Ipsum omnis illo beatae sit consectetur non vitae eum quia reiciendis ut ullamco animi deserunt fugit possimus qui eu	0.00	0.00	70.00	35.00	37.00	\N	\N	{"montant":4,"devise":"FCFA"}	[]	["uytr"]	["Eum nemo autem qui quidem sed explicabo Aut non id repudiandae odit tempora anim"]	["Iste sit necessitatibus aperiam quisquam ut voluptas quae ex enim voluptas distinctio Sit et"]	[]	f	2025-07-24 20:47:49	2025-07-24 21:10:46	2025-07-24 21:10:46	f
16	27	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	Magni exercitation ea corrupti quisquam molestiae	Excepturi eiusmod quis culpa sint	simple	59	dfddsd	dsfds	sfds	dfsds	dfsd	dsfd	dsfds	dsfds	\N	dfsfd	dsfd	dsfds	fsfds	dfsd	\N	fsdfs	test	test	dsfds	fdsf	0.00	0.00	1.00	64.00	69.00	\N	\N	{"montant":33,"devise":"FCFA"}	[]	["test"]	["sfdsd"]	["trezd"]	[]	f	2025-07-24 21:16:35	2025-07-25 10:13:46	2025-07-25 10:13:46	f
13	27	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	que cumque mollit nostrud ut et	niti dolor similique enim	simple	23	Nulla veniam est proident dolorem recusandae Eveniet earum facilis sunt consequat Sit dicta eum maxime consequatur amet	Incidunt sunt earum tempor eu qui at consequuntur eos laboriosam esse officia quaerat voluptate nostrum eos fugiat ea reiciendis minima	Consequuntur nostrud molestias suscipit nobis aut dolorem dolorum illo ut ipsum in	Voluptatum nostrud sed et enim harum eaque qui molestiae excepturi	Pariatur At sed voluptatem eos natus tempora est nihil aut	Sed quia quod expedita nostrum error repudiandae explicabo Facilis distinctio Pariatur Eu	Ea provident quis vero cupidatat	Quis iste provident vitae nostrum quaerat dolore eiusmod porro eius	\N	Pariatur Aute nulla atque sed voluptatum do officia voluptatibus ad et nesciunt totam illum ratione	Optio quia voluptates velit aliquam facilis nemo in soluta eos totam optio sint inventore	Non voluptatibus a sed Nam ut porro eiusmod ullam repellendus Commodi inventore	Animi aspernatur ea dolor aute molestias	Odio alias est commodo nulla cillum consectetur qui quo et proident adipisci nihil et aliqua	\N	Sed rerum et sed sequi autem fugiat nulla quod eum molestiae eveniet duis accusantium corrupti est delectus quos	Qui sint nihil enim ut	Id nihil quod soluta expedita sit tempore iusto magnam sapiente enim quia aperiam qui exercitation ad qui corporis	Mollit quia totam illum laboris labore necessitatibus ea iusto sed	Obcaecati fugiat quas quia est beatae voluptates exercitation enim adipisicing alias labore	0.00	0.00	52.00	9.00	66.00	\N	\N	{"montant":5,"devise":"FCFA"}	[]	["Nisi non magnam doloribus sit corporis ratione suscipit consequuntur eu ea nobis est culpa doloremque id dicta quae vero incidunt"]	["Voluptatem quisquam minima qui quam incidunt voluptatem sed"]	["Nisi qui sint nesciunt magni dolorem autem vitae eius molestiae nulla sunt fuga Ipsam"]	[]	f	2025-07-24 20:17:45	2025-07-25 10:13:50	2025-07-25 10:13:50	f
19	27	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	Modi laborum Nobis perspiciatis sit totam animi vel aliquid occaecat voluptatem aspernatur quam qui sunt ab in sed reprehenderit voluptatem	Omnis sit odit optio dolore officiis dolorum est	simple	87	Temporibus corporis dolor dolores veritatis ea error nisi vel esse occaecat impedit ipsam ut fugiat dolorem sint qui reprehenderit	Sint odit nobis sunt inventore a amet anim repudiandae dolorem laboris quos et a	Hic quibusdam qui eaque sapiente sit pariatur Harum non sunt aut perferendis aut commodo quam sint sit dolore cillum	Aut dolorem do non minus labore illum architecto in dignissimos ea quia qui	Fuga Voluptas dolor ut et pariatur Libero accusantium nulla magni duis qui nisi laudantium commodo iure incidunt	Voluptates voluptate corrupti quis voluptas qui atque natus non provident eiusmod consequatur Dolore eius veniam molestiae	Facilis architecto ad consectetur quod tempor corrupti laboriosam optio	Qui ducimus commodi nisi maxime consequatur veniam nihil	\N	Dolorem in proident cumque hic sed corrupti obcaecati incidunt magnam vero velit ex sint nostrum	Suscipit dolores aliquid provident minim dignissimos voluptatem vitae magni itaque	Iste officia tempore ut beatae et repudiandae aliqua Eu distinctio Ut natus laborum ea deserunt duis	Sit sed debitis sapiente aut dolore aliquid culpa ab cum maxime quia in quos placeat deleniti	Velit assumenda accusantium eligendi blanditiis id quam	\N	Dolorum corporis accusamus error cum itaque repellendus Eos atque	Elit rerum veniam quas quis excepteur sed dolore nihil ullam qui	Sint do delectus magnam quisquam labore	Nihil ut ea nihil recusandae Fugiat veritatis dolores nisi veritatis delectus ullamco perferendis do voluptatem Nulla non	Voluptatem non voluptatem fugit ullam reprehenderit numquam commodi debitis facere et asperiores esse et	0.00	0.00	3.00	10.00	78.00	\N	\N	{"montant":68,"devise":"FCFA"}	[]	["Ipsum voluptas sunt consequat Proident harum explicabo Deserunt accusantium dolor aut fuga Mollit iusto quia distinctio Incididunt unde vel"]	["Soluta minim cumque officia non aut ut laborum Nam corrupti quis labore voluptatem Id quia sed"]	["Dolor in omnis enim qui sint esse a aliquam deleniti aut magna"]	[]	f	2025-07-25 10:50:17	2025-07-25 16:28:18	2025-07-25 16:28:18	f
17	28	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	Voluptas reprehenderit aut delectus perferendis expedita enim in possimus voluptatibus consequat Similique eaque omnis	Aute laboris unde ex ut veniam nihil quidem velit	simple	82	Odio consequatur Voluptas voluptates in porro excepturi qui dicta non ipsam distinctio Quis ad quasi voluptates dolore	Ex in autem iusto optio ipsam lorem commodo proident ut perspiciatis rerum dolores	Fugiat inventore iure eos incidunt blanditiis ipsa consequatur Impedit	Dolorem ipsum cillum reprehenderit adipisicing et minus accusantium odio mollit aut est qui ut hic tempora amet est	Est aliquid voluptas facere dolore voluptatibus id rem et do laudantium Nam quam sequi	Dolorem ex amet eum repudiandae veniam quis error quidem aliquid consectetur ducimus incidunt sit sunt aut quas sunt quisquam dolore	Dolorum aut dolorum et aut itaque in illum omnis omnis	Veritatis esse fuga Irure iure magna nesciunt ut et qui eos voluptates dolorum hic illum blanditiis non	\N	Deserunt architecto quo voluptate sed error esse natus pariatur Eum exercitationem	Autem accusantium sit est qui	Est consequatur quibusdam aliquid amet distinctio Ut soluta voluptate sunt dolores	Aut et molestiae nostrud rerum officia reiciendis mollitia qui harum voluptates at sit ratione	Aut adipisicing quod veritatis cum aliquam voluptatem Aut tempor eius blanditiis blanditiis culpa at est eos	\N	Modi autem velit est est quam illum dolorem	Autem dolor non nisi qui sunt eu ipsum maiores dolor voluptatem dolore	Est sed rerum tempora voluptatem Quas atque consectetur proident itaque	Officia quo pariatur Architecto modi culpa enim molestiae minim consequatur Minim	Error perspiciatis quia aut dolore aliquam iure ex obcaecati cumque corporis soluta lorem earum iure	0.00	0.00	98.00	6.00	17.00	\N	\N	{"montant":74,"devise":"FCFA"}	[]	["Provident est ea et sint blanditiis recusandae Accusamus tenetur voluptate"]	["In adipisicing id eius molestiae consequatur mollit debitis et eos ut expedita magni asperiores hic"]	["Cumque est aute voluptatibus quod minim"]	[]	f	2025-07-25 10:15:00	2025-07-25 16:28:29	2025-07-25 16:28:29	f
20	27	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	Modi laborum Nobis pes sit totam animi vel aliquid occaecat voluptatem aspernatur quam qui sunt ab in sed reprehenderit voluptatem	Omnis sit odit opt dolore officiis dolorum est	simple	87	Temporibus corporis dolor dolores veritatis ea error nisi vel esse occaecat impedit ipsam ut fugiat dolorem sint qui reprehenderit	Sint odit nobis sunt inventore a amet anim repudiandae dolorem laboris quos et a	Hic quibusdam qui eaque sapiente sit pariatur Harum non sunt aut perferendis aut commodo quam sint sit dolore cillum	Aut dolorem do non minus labore illum architecto in dignissimos ea quia qui	Fuga Voluptas dolor ut et pariatur Libero accusantium nulla magni duis qui nisi laudantium commodo iure incidunt	Voluptates voluptate corrupti quis voluptas qui atque natus non provident eiusmod consequatur Dolore eius veniam molestiae	Facilis architecto ad consectetur quod tempor corrupti laboriosam optio	Qui ducimus commodi nisi maxime consequatur veniam nihil	\N	Dolorem in proident cumque hic sed corrupti obcaecati incidunt magnam vero velit ex sint nostrum	Suscipit dolores aliquid provident minim dignissimos voluptatem vitae magni itaque	Iste officia tempore ut beatae et repudiandae aliqua Eu distinctio Ut natus laborum ea deserunt duis	Sit sed debitis sapiente aut dolore aliquid culpa ab cum maxime quia in quos placeat deleniti	Velit assumenda accusantium eligendi blanditiis id quam	\N	Dolorum corporis accusamus error cum itaque repellendus Eos atque	Elit rerum veniam quas quis excepteur sed dolore nihil ullam qui	Sint do delectus magnam quisquam labore	Nihil ut ea nihil recusandae Fugiat veritatis dolores nisi veritatis delectus ullamco perferendis do voluptatem Nulla non	Voluptatem non voluptatem fugit ullam reprehenderit numquam commodi debitis facere et asperiores esse et	0.00	0.00	3.00	10.00	78.00	\N	\N	{"montant":68,"devise":"FCFA"}	[]	["Ipsum voluptas sunt consequat Proident harum explicabo Deserunt accusantium dolor aut fuga Mollit iusto quia distinctio Incididunt unde vel"]	["Soluta minim cumque officia non aut ut laborum Nam corrupti quis labore voluptatem Id quia sed"]	["Dolor in omnis enim qui sint esse a aliquam deleniti aut magna"]	[]	f	2025-07-25 10:54:17	2025-07-25 15:42:15	2025-07-25 15:42:15	f
27	27	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	Delectus quis ratione quae est voluptatum excepturi quis porro odit	Cum nisi rerum facilis ipsam maiores rerum veritat	simple	86	Cupidatat consequatur ut in voluptatum quibusdam enim quidem magnam mollit consequatur do consequat Praesentium incidunt voluptas cumque corporis	Duis fugit minim ea autem numquam hic occaecat temporibus in fugiat non fuga	Et et in eos est voluptatem Ut aut aut blanditiis voluptate voluptatum magnam est omnis unde quibusdam	Qui natus cupidatat exercitation id ipsum cum earum magni ut	Quis alias necessitatibus perspiciatis error fugiat facilis	Nihil recusandae Sed id magna rerum et reiciendis in veniam aliquip esse	Officia sed hic atque sed commodo autem sapiente illum quo ut aliqua Ab	Quo atque vel itaque illo voluptatum est adipisci vero qui enim aut nemo	\N	Dolore labore delectus quis magnam rem elit velit consequatur lorem suscipit dolor	Dolorem placeat blanditiis laborum maxime beatae dolor eius quaerat sapiente corrupti necessitatibus et atque	Sit laboris explicabo Atque excepturi quam pariatur Rerum amet omnis est ut labore mollitia suscipit autem	Non voluptate autem officia lorem recusandae Laboriosam laborum qui amet nobis et dignissimos	Earum eos cumque necessitatibus cillum perferendis adipisicing ullam aperiam ullam maxime sit voluptas iusto	\N	Ut ex mollitia nihil velit anim vel	Perferendis adipisicing ex rerum aliquam duis non sed sunt	Delectus tempora et unde iste suscipit laborum qui nemo	Dolore in laudantium eiusmod magnam	Magnam aperiam explicabo Unde non itaque sed quas	0.00	0.00	13.00	70.00	28.00	\N	\N	{"montant":56,"devise":"FCFA"}	[]	["Exercitationem et omnis veniam minus vel debitis impedit sint tempore magni aute omnis veniam adipisicing quis et dolore consequatur"]	["Eligendi in sed dolores voluptatem corporis"]	["Cupiditate est saepe quis tenetur quod fugit perferendis similique dolorum veniam voluptatem Nam"]	[]	f	2025-07-25 11:59:40	2025-07-25 16:28:23	2025-07-25 16:28:23	f
41	\N	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	Nihil lorem porro doloribus et neque labore eum minima atque enim cupiditate sit temporibus optio fugiat earum	Fugit unde aut iste dolor voluptas exercitation q	simple	68	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	0.00	0.00	26.00	93.00	11.00	\N	\N	{"montant":4,"devise":"FCFA"}	[]	[]	[]	[]	[]	f	2025-07-25 20:22:04	2025-07-25 20:22:26	2025-07-25 20:22:26	f
26	28	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	Sit ex ad modi expedita sunt eiusmod laudantium quod dolor sed ut aut proident	Sapiente aliquid in vitae laborum Explicabo Ut a	simple	67	Nisi vero laboris minima voluptatibus tempora voluptates vero vel aute aliquip	Velit excepteur nisi lorem eiusmod placeat odit facilis excepteur in	Aut ullam vel magna minus quos excepteur amet enim	Et vero officia natus et itaque blanditiis culpa in aut sit	Ipsum sit ullamco nulla duis id cupiditate dolorem quos nemo eiusmod totam quidem autem ullam consectetur ad exercitationem	Ut aute quia est quidem inventore esse eiusmod impedit	Reiciendis in aliqua Quia quis anim voluptatem voluptas et cupidatat excepturi amet quis veniam consectetur et vero Nam velit	Dolores dolorum expedita aut dicta corrupti consectetur accusamus proident sapiente ab nisi nisi non distinctio	\N	Ut quo minus illum occaecat sint excepturi est fugiat quo vel porro officia laudantium qui omnis reiciendis	Recusandae Aut dignissimos delectus cillum ipsum commodi	Debitis est fuga Tenetur cupidatat maiores illum laboris asperiores voluptatem est sit tempore consectetur nostrud nemo	Et omnis deleniti laborum Nulla ut et	Fugiat consequatur lorem aut sint sit id ipsa doloribus veniam provident et ut nostrum sint	\N	Est rem error quia aut	Voluptate ipsum quis eveniet quo aliquid voluptatem quibusdam sint occaecat qui	Non aperiam sint consequatur Nihil qui	Ex labore error est incidunt quas esse a sit rerum facilis possimus soluta assumenda et ducimus Nam	Autem consequuntur suscipit sunt distinctio Est consequatur natus vero anim saepe sint mollitia modi quis officia impedit aperiam debitis ullamco	0.00	0.00	78.00	92.00	16.00	\N	\N	{"montant":78,"devise":"FCFA"}	[]	["Numquam aut quia quae totam culpa"]	["Irure quaerat suscipit maxime harum pariatur Laudantium deserunt vero"]	["Aspernatur esse et temporibus et eos distinctio Amet Nam libero itaque"]	[]	f	2025-07-25 11:17:37	2025-07-25 15:42:19	2025-07-25 15:42:19	f
21	27	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	Modi laborum No pes sit totam animi vel aliquid occaecat voluptatem aspernatur quam qui sunt ab in sed reprehenderit voluptatem	Omnis sit odit opt dolore officiis dorum est	simple	87	Temporibus corporis dolor dolores veritatis ea error nisi vel esse occaecat impedit ipsam ut fugiat dolorem sint qui reprehenderit	Sint odit nobis sunt inventore a amet anim repudiandae dolorem laboris quos et a	Hic quibusdam qui eaque sapiente sit pariatur Harum non sunt aut perferendis aut commodo quam sint sit dolore cillum	Aut dolorem do non minus labore illum architecto in dignissimos ea quia qui	Fuga Voluptas dolor ut et pariatur Libero accusantium nulla magni duis qui nisi laudantium commodo iure incidunt	Voluptates voluptate corrupti quis voluptas qui atque natus non provident eiusmod consequatur Dolore eius veniam molestiae	Facilis architecto ad consectetur quod tempor corrupti laboriosam optio	Qui ducimus commodi nisi maxime consequatur veniam nihil	\N	Dolorem in proident cumque hic sed corrupti obcaecati incidunt magnam vero velit ex sint nostrum	Suscipit dolores aliquid provident minim dignissimos voluptatem vitae magni itaque	Iste officia tempore ut beatae et repudiandae aliqua Eu distinctio Ut natus laborum ea deserunt duis	Sit sed debitis sapiente aut dolore aliquid culpa ab cum maxime quia in quos placeat deleniti	Velit assumenda accusantium eligendi blanditiis id quam	\N	Dolorum corporis accusamus error cum itaque repellendus Eos atque	Elit rerum veniam quas quis excepteur sed dolore nihil ullam qui	Sint do delectus magnam quisquam labore	Nihil ut ea nihil recusandae Fugiat veritatis dolores nisi veritatis delectus ullamco perferendis do voluptatem Nulla non	Voluptatem non voluptatem fugit ullam reprehenderit numquam commodi debitis facere et asperiores esse et	0.00	0.00	3.00	10.00	78.00	\N	\N	{"montant":68,"devise":"FCFA"}	[]	["Ipsum voluptas sunt consequat Proident harum explicabo Deserunt accusantium dolor aut fuga Mollit iusto quia distinctio Incididunt unde vel"]	["Soluta minim cumque officia non aut ut laborum Nam corrupti quis labore voluptatem Id quia sed"]	["Dolor in omnis enim qui sint esse a aliquam deleniti aut magna"]	[]	f	2025-07-25 10:54:34	2025-07-25 15:42:22	2025-07-25 15:42:22	f
31	28	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	Rerum alias asperiores culpa temporibus consequatur Provident pariatur Numquam nesciunt aute enim et voluptatem aut ipsum omnis	Aliquid facilis nisi voluptas aliquam consequatur	simple	93	Perferendis molestiae accusamus qui quidem officiis totam laudantium excepturi inventore aut saepe	Suscipit irure veniam fugiat sunt cillum sit pariatur Dolores ex	Sit consequat Ut animi adipisci odio similique quo iure dicta ex	Ratione labore dignissimos repellendus Ut lorem reiciendis	Obcaecati non elit elit placeat reiciendis quia ullam voluptatem fugiat anim odio eius iste	Eum architecto sit tempore ut	Quasi omnis doloribus eos sed mollit quibusdam deleniti beatae eius id in ad id	Qui cillum maiores dicta eum vel exercitation dolorem	\N	Consectetur quidem cupidatat nisi aperiam culpa aliquip laboris rerum dolor id minim non voluptatem Aliqua	Ut dolores beatae sunt commodi cillum veniam et aut nesciunt nulla ab minus et doloremque doloribus quis	Dolorem dolore aliquid quibusdam saepe quae dolor quos perferendis est ut fugiat in reprehenderit consequatur facilis sed dolorem saepe sit	Voluptas quia iusto ea laboris ea ea quisquam aliquam molestiae nesciunt accusantium lorem	Voluptatem tempor minus sed est id repellendus Minima laboriosam animi blanditiis iste voluptate quod	\N	Dolor ducimus sit tempor illum architecto officiis quibusdam	Quia ut est sit ut ut ad facilis mollitia enim nobis	Porro tempore necessitatibus impedit dolorem pariatur Sunt sapiente culpa fugit dolore vel quasi	Possimus a fugit dolor ut non cumque nisi excepteur in voluptas sed et ipsum quasi iusto anim accusamus voluptates eaque	Quia alias nihil est voluptate dolore cum aut praesentium pariatur Placeat	0.00	0.00	98.00	32.00	60.00	\N	\N	{"montant":60,"devise":"FCFA"}	[]	["Incididunt et officia ut nisi nihil in"]	["Natus earum sint cillum sint sint rerum id rem ut voluptatibus aliquid dolore perspiciatis delectus accusamus ipsam iure perspiciatis"]	["Rerum officia Nam distinctio Temporibus consequuntur et inventore labore sequi aut"]	[]	f	2025-07-25 16:07:47	2025-07-25 16:07:59	2025-07-25 16:07:59	f
33	27	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	Sit quae anim optio illo ad molestias quibusdam reprehenderit ea nihil incidunt non asperiores	Iusto neque eius cupiditate nihil saepe ea rem ut	simple	59	Id laudantium est quod explicabo Animi fuga Sit ipsum architecto magni quo ratione quam odit	Neque praesentium quasi labore anim sit lorem ex id esse esse quaerat nostrud ea eveniet est	Velit sunt nisi ex animi ex libero aut atque	Maiores quos minima blanditiis voluptas sit	Quos nulla tempore sint consequatur eu obcaecati voluptatem	Reprehenderit ea ipsum ut ad rem soluta excepturi velit voluptatem dolor cupiditate aspernatur vitae quia	Expedita magna tenetur quisquam in eaque amet corrupti vel laudantium ullam magna et hic autem cum dolor	Praesentium enim eos repellendus Explicabo Dignissimos in ut Nam ipsum nesciunt	\N	Adipisci doloribus dolor esse fuga Ut rerum	Reprehenderit qui consequatur Ratione nobis cumque temporibus cillum	Dignissimos consequatur distinctio Sint doloremque modi eum consequatur voluptatem sed	Et fuga In fugiat fugiat rerum elit	Tempora sed ex nobis vel dolore occaecat molestiae dolorum officia possimus do	\N	Dolorum commodo enim alias et quasi nisi maiores ut quae ullamco quis voluptatem est consequatur	Deleniti eiusmod ex architecto duis	Totam atque unde qui cupiditate in atque quam veritatis non sint officia	Cupiditate nostrud eum do duis reiciendis quam porro aliquam aliquam optio quasi temporibus cillum voluptatem quas harum quod officia iusto	Quam dolor amet et cillum officia quia eum sed consequatur	0.00	0.00	35.00	41.00	2.00	\N	\N	{"montant":95,"devise":"FCFA"}	[]	["Voluptatibus impedit ducimus saepe obcaecati ea nobis et sit possimus labore autem magnam"]	["Excepturi reiciendis dicta nihil neque ad placeat fuga Eiusmod omnis quibusdam explicabo Quia eos ipsam est cumque deserunt"]	["Esse perferendis eos ea quod eaque magni sit excepturi a delectus dolorem ipsam repudiandae laborum velit"]	[]	f	2025-07-25 16:12:16	2025-07-25 16:13:07	2025-07-25 16:13:07	f
32	28	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	Et autem mollit dolore voluptatibus id quod ad aut suscipit esse	Eius et irure natus nostrum sit laboris voluptas	simple	77	Non laboriosam reiciendis provident in debitis facilis id odio quaerat dolore quos non accusantium aut eum aperiam eum fugiat	Suscipit cum non et perspiciatis accusamus nihil ullam et alias veniam nostrud non sint incidunt Nam mollitia exercitation eos ut	Libero repellendus Id veniam tempora nisi nihil excepteur vitae lorem voluptatum autem dolores	Omnis velit quis sed voluptas suscipit est doloribus non do eiusmod voluptatem	Cupiditate ipsum id eaque ex incidunt earum inventore ipsum ullamco quae qui omnis aspernatur quo	Ratione possimus ut optio provident nemo qui elit in nisi voluptate amet nostrum	Enim consequuntur deserunt quo illum doloremque ut culpa labore sapiente exercitationem	Ipsam non sed lorem amet pariatur Rerum et provident est consequatur Numquam nostrud veritatis architecto similique consectetur qui	\N	Nulla sint molestiae voluptate aliqua Animi excepturi duis reprehenderit et dolor at omnis qui	Officia sit nobis sunt aut et quod in aut quisquam id consectetur et ut	Nobis laborum id dolores numquam tenetur quo suscipit autem asperiores dolorem commodi consequat In illo incididunt	Incidunt aliqua Voluptate sint rerum	Quia id id aut perspiciatis sapiente ad qui nihil est quis in commodo deserunt possimus totam qui esse ducimus minima	\N	Sint molestias occaecat ut modi doloremque ut occaecat aliquid est	Earum molestiae aut esse dolor sint dolorem	Ex ullam voluptate rem eligendi in quibusdam iste reprehenderit at	Dolor anim totam consectetur qui quam est vero perspiciatis velit quas nesciunt molestiae minus ut	Ut nostrum ut doloribus nihil	0.00	0.00	60.00	0.00	9.00	\N	\N	{"montant":86,"devise":"FCFA"}	[]	["Non placeat ea enim eos minima ut non culpa voluptatem pariatur Reiciendis fugiat"]	["Voluptatum et provident eum magnam fugiat id rerum laboris ad omnis adipisci"]	["Non enim molestiae voluptate exercitation quia exercitation"]	[]	f	2025-07-25 16:10:21	2025-07-25 16:13:31	2025-07-25 16:13:31	f
34	27	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	Fuga Pariatur Blanditiis irure qui sint anim reprehenderit commodi necessitatibus commodi numquam minima quas magna ad modi veniam aut	Id do suscipit repellendus Magnam autem placeat	simple	70	Dolorum ea ipsum quis porro modi ducimus	Neque beatae beatae labore velit velit odio ut dolor aut aspernatur ipsa ut sit fugiat adipisicing provident natus cupiditate quas	Asperiores velit pariatur Voluptates non aut velit sed saepe	Laboriosam do animi dolor asperiores adipisicing illo sed in voluptas officia laboris sed culpa nesciunt veniam	Distinctio Minus in enim dolor consectetur aspernatur commodi perspiciatis	Vel exercitationem optio eos mollit at ipsa omnis recusandae	Eum molestias rerum culpa deleniti eum tempor ea cillum labore	Laudantium esse et tenetur aliqua Molestias est qui nesciunt est	\N	Aute autem perspiciatis officia alias commodo aut voluptatem	Quo soluta laborum Ipsam et ipsum elit libero enim ad est dolore nisi officia	Ut soluta aut ut dolor totam deserunt esse incidunt laboriosam temporibus culpa quisquam proident	Sed pariatur Lorem laboriosam excepteur quae voluptatem voluptate dicta perferendis repellendus Asperiores pariatur In et sit quaerat labore	Amet aperiam et dolore natus aute ut dolor laudantium sunt ut lorem ullam quis alias voluptate sed	\N	Irure nostrum est dolor quisquam ipsam qui nihil sed in proident vel autem dolore excepturi beatae	Dolores inventore impedit necessitatibus voluptatibus earum nulla nisi est in porro aut non non culpa iste nulla	Sint quaerat nulla est laborum eaque esse nihil eos rerum veniam vel	Magni quibusdam recusandae Occaecat quia ullam ab modi veniam	Rem recusandae Quae quo excepteur reprehenderit aliqua Officia	0.00	0.00	30.00	75.00	15.00	\N	\N	{"montant":69,"devise":"FCFA"}	[]	["Ratione consequat Eligendi sint et tempore iste voluptatibus lorem atque quasi eligendi eveniet pariatur Dolore sint est minus adipisicing obcaecati"]	["Beatae et facilis quis ipsum sunt illum officiis est voluptate suscipit veniam"]	["Aut provident quo rem voluptas nihil molestias distinctio Quia dolor voluptate duis ex commodo reprehenderit"]	[]	f	2025-07-25 16:29:19	2025-07-25 16:40:23	2025-07-25 16:40:23	f
35	27	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	Voluptate qui cum qui aliquam velit	Quo ex laborum quo nostrum dolor autem et minim si	simple	66	Adipisicing elit incidunt sequi dolor ut maiores ut qui explicabo Quo consequatur sint voluptatem aut eos modi deserunt adipisci	Quidem reprehenderit quaerat et quo	Est et est in eos nesciunt sit ipsum qui deleniti quas fuga In nisi ut quis	Laborum Placeat repellendus Est quod	Aliquid ad fugiat error enim labore et dignissimos neque nisi deleniti	Assumenda odit modi quos incidunt ipsum perspiciatis rerum obcaecati aut reiciendis	Asperiores eos voluptas quia repellendus Ex	Aut harum proident odit quo excepteur tempor ducimus impedit quam	\N	Eiusmod quia quia ex aspernatur ut et ipsam iste error consectetur	Non aut eaque tempore itaque deserunt commodi amet	Quo et totam lorem repudiandae officia cupidatat est	Mollitia tenetur dolor consequatur et reprehenderit modi vero quas dignissimos mollitia	Culpa incidunt porro voluptatibus iure veniam sunt	\N	Illum ut incidunt consequatur occaecat excepturi aut eligendi voluptatem excepturi hic error officiis	Ipsam nihil deserunt iure architecto consequatur dolor	Dignissimos et dolorem nobis numquam nisi tempor ut expedita quis odit repudiandae qui voluptas in rem ipsum culpa omnis	Dolorem ipsum quia perferendis porro non	Amet corrupti labore incididunt nihil optio et aliquip	0.00	0.00	84.00	18.00	92.00	\N	\N	{"montant":39,"devise":"FCFA"}	[]	["Ipsum possimus sint quia ea"]	["Non ut tempore eum sunt modi nihil necessitatibus iste asperiores consectetur et"]	["Expedita labore maxime labore ut reprehenderit ad at qui facere numquam magna officia dolorem nemo nulla ex quia laboris cupidatat"]	[]	f	2025-07-25 16:43:25	2025-07-25 16:50:35	2025-07-25 16:50:35	f
37	27	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	Modi labs sit totam animi vel aliquid occaecat voluptatem aspernatur quam qui sunt ab in sed reprehenderit voluptatem	Omnis sit odit opt dolore ofs dorum est	simple	87	Temporibus corporis dolor dolores veritatis ea error nisi vel esse occaecat impedit ipsam ut fugiat dolorem sint qui reprehenderit	Sint odit nobis sunt inventore a amet anim repudiandae dolorem laboris quos et a	Hic quibusdam qui eaque sapiente sit pariatur Harum non sunt aut perferendis aut commodo quam sint sit dolore cillum	Aut dolorem do non minus labore illum architecto in dignissimos ea quia qui	Fuga Voluptas dolor ut et pariatur Libero accusantium nulla magni duis qui nisi laudantium commodo iure incidunt	Voluptates voluptate corrupti quis voluptas qui atque natus non provident eiusmod consequatur Dolore eius veniam molestiae	Facilis architecto ad consectetur quod tempor corrupti laboriosam optio	Qui ducimus commodi nisi maxime consequatur veniam nihil	\N	Dolorem in proident cumque hic sed corrupti obcaecati incidunt magnam vero velit ex sint nostrum	Suscipit dolores aliquid provident minim dignissimos voluptatem vitae magni itaque	Iste officia tempore ut beatae et repudiandae aliqua Eu distinctio Ut natus laborum ea deserunt duis	Sit sed debitis sapiente aut dolore aliquid culpa ab cum maxime quia in quos placeat deleniti	Velit assumenda accusantium eligendi blanditiis id quam	\N	Dolorum corporis accusamus error cum itaque repellendus Eos atque	Elit rerum veniam quas quis excepteur sed dolore nihil ullam qui	Sint do delectus magnam quisquam labore	Nihil ut ea nihil recusandae Fugiat veritatis dolores nisi veritatis delectus ullamco perferendis do voluptatem Nulla non	Voluptatem non voluptatem fugit ullam reprehenderit numquam commodi debitis facere et asperiores esse et	0.00	0.00	3.00	10.00	78.00	\N	\N	{"montant":68,"devise":"FCFA"}	[]	["Ipsum voluptas sunt consequat Proident harum explicabo Deserunt accusantium dolor aut fuga Mollit iusto quia distinctio Incididunt unde vel"]	["Soluta minim cumque officia non aut ut laborum Nam corrupti quis labore voluptatem Id quia sed"]	["Dolor in omnis enim qui sint esse a aliquam deleniti aut magna"]	[]	f	2025-07-25 17:27:39	2025-07-25 19:37:15	2025-07-25 19:37:15	f
36	27	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	Modi laborum No pes sit totam animi vel aliquid occaecat voluptatem aspernatur quam qui sunt ab in sed reprehenderit voluptatem	Omnis sit odit opt dolore officiis dorum est	simple	87	Temporibus corporis dolor dolores veritatis ea error nisi vel esse occaecat impedit ipsam ut fugiat dolorem sint qui reprehenderit	Sint odit nobis sunt inventore a amet anim repudiandae dolorem laboris quos et a	Hic quibusdam qui eaque sapiente sit pariatur Harum non sunt aut perferendis aut commodo quam sint sit dolore cillum	Aut dolorem do non minus labore illum architecto in dignissimos ea quia qui	Fuga Voluptas dolor ut et pariatur Libero accusantium nulla magni duis qui nisi laudantium commodo iure incidunt	Voluptates voluptate corrupti quis voluptas qui atque natus non provident eiusmod consequatur Dolore eius veniam molestiae	Facilis architecto ad consectetur quod tempor corrupti laboriosam optio	Qui ducimus commodi nisi maxime consequatur veniam nihil	\N	Dolorem in proident cumque hic sed corrupti obcaecati incidunt magnam vero velit ex sint nostrum	Suscipit dolores aliquid provident minim dignissimos voluptatem vitae magni itaque	Iste officia tempore ut beatae et repudiandae aliqua Eu distinctio Ut natus laborum ea deserunt duis	Sit sed debitis sapiente aut dolore aliquid culpa ab cum maxime quia in quos placeat deleniti	Velit assumenda accusantium eligendi blanditiis id quam	\N	Dolorum corporis accusamus error cum itaque repellendus Eos atque	Elit rerum veniam quas quis excepteur sed dolore nihil ullam qui	Sint do delectus magnam quisquam labore	Nihil ut ea nihil recusandae Fugiat veritatis dolores nisi veritatis delectus ullamco perferendis do voluptatem Nulla non	Voluptatem non voluptatem fugit ullam reprehenderit numquam commodi debitis facere et asperiores esse et	0.00	0.00	3.00	10.00	78.00	\N	\N	{"montant":68,"devise":"FCFA"}	[]	["Ipsum voluptas sunt consequat Proident harum explicabo Deserunt accusantium dolor aut fuga Mollit iusto quia distinctio Incididunt unde vel"]	["Soluta minim cumque officia non aut ut laborum Nam corrupti quis labore voluptatem Id quia sed"]	["Dolor in omnis enim qui sint esse a aliquam deleniti aut magna"]	[]	f	2025-07-25 16:51:49	2025-07-25 18:15:15	2025-07-25 18:15:15	f
39	27	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	Modi labs sittam animi vel aliquid occaecat voluptatem aspernatur quam qui sunt ab in sed reprehenderit voluptatem	Omnis sit odit opt doklore ofs dorum est	simple	87	Temporibus corporis dolor dolores veritatis ea error nisi vel esse occaecat impedit ipsam ut fugiat dolorem sint qui reprehenderit	Sint odit nobis sunt inventore a amet anim repudiandae dolorem laboris quos et a	Hic quibusdam qui eaque sapiente sit pariatur Harum non sunt aut perferendis aut commodo quam sint sit dolore cillum	Aut dolorem do non minus labore illum architecto in dignissimos ea quia qui	Fuga Voluptas dolor ut et pariatur Libero accusantium nulla magni duis qui nisi laudantium commodo iure incidunt	Voluptates voluptate corrupti quis voluptas qui atque natus non provident eiusmod consequatur Dolore eius veniam molestiae	Facilis architecto ad consectetur quod tempor corrupti laboriosam optio	Qui ducimus commodi nisi maxime consequatur veniam nihil	\N	Dolorem in proident cumque hic sed corrupti obcaecati incidunt magnam vero velit ex sint nostrum	Suscipit dolores aliquid provident minim dignissimos voluptatem vitae magni itaque	Iste officia tempore ut beatae et repudiandae aliqua Eu distinctio Ut natus laborum ea deserunt duis	Sit sed debitis sapiente aut dolore aliquid culpa ab cum maxime quia in quos placeat deleniti	Velit assumenda accusantium eligendi blanditiis id quam	\N	Dolorum corporis accusamus error cum itaque repellendus Eos atque	Elit rerum veniam quas quis excepteur sed dolore nihil ullam qui	Sint do delectus magnam quisquam labore	Nihil ut ea nihil recusandae Fugiat veritatis dolores nisi veritatis delectus ullamco perferendis do voluptatem Nulla non	Voluptatem non voluptatem fugit ullam reprehenderit numquam commodi debitis facere et asperiores esse et	0.00	0.00	3.00	10.00	78.00	\N	\N	{"montant":68,"devise":"FCFA"}	[]	["Ipsum voluptas sunt consequat Proident harum explicabo Deserunt accusantium dolor aut fuga Mollit iusto quia distinctio Incididunt unde vel"]	["Soluta minim cumque officia non aut ut laborum Nam corrupti quis labore voluptatem Id quia sed"]	["Dolor in omnis enim qui sint esse a aliquam deleniti aut magna"]	[]	f	2025-07-25 18:15:38	2025-07-25 18:15:38	\N	f
40	27	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	Modi labs sit vel aliquid occaecat voluptatem aspernatur quam qui sunt ab in sed reprehenderit voluptatem	Omnis sit odit opt doklore ofs dorumjhgf est	simple	87	Temporibus corporis dolor dolores veritatis ea error nisi vel esse occaecat impedit ipsam ut fugiat dolorem sint qui reprehenderit	Sint odit nobis sunt inventore a amet anim repudiandae dolorem laboris quos et a	Hic quibusdam qui eaque sapiente sit pariatur Harum non sunt aut perferendis aut commodo quam sint sit dolore cillum	Aut dolorem do non minus labore illum architecto in dignissimos ea quia qui	Fuga Voluptas dolor ut et pariatur Libero accusantium nulla magni duis qui nisi laudantium commodo iure incidunt	Voluptates voluptate corrupti quis voluptas qui atque natus non provident eiusmod consequatur Dolore eius veniam molestiae	Facilis architecto ad consectetur quod tempor corrupti laboriosam optio	Qui ducimus commodi nisi maxime consequatur veniam nihil	\N	Dolorem in proident cumque hic sed corrupti obcaecati incidunt magnam vero velit ex sint nostrum	Suscipit dolores aliquid provident minim dignissimos voluptatem vitae magni itaque	Iste officia tempore ut beatae et repudiandae aliqua Eu distinctio Ut natus laborum ea deserunt duis	Sit sed debitis sapiente aut dolore aliquid culpa ab cum maxime quia in quos placeat deleniti	Velit assumenda accusantium eligendi blanditiis id quam	\N	Dolorum corporis accusamus error cum itaque repellendus Eos atque	Elit rerum veniam quas quis excepteur sed dolore nihil ullam qui	Sint do delectus magnam quisquam labore	Nihil ut ea nihil recusandae Fugiat veritatis dolores nisi veritatis delectus ullamco perferendis do voluptatem Nulla non	Voluptatem non voluptatem fugit ullam reprehenderit numquam commodi debitis facere et asperiores esse et	0.00	0.00	3.00	10.00	78.00	\N	\N	{"montant":68,"devise":"FCFA"}	[]	["Ipsum voluptas sunt consequat Proident harum explicabo Deserunt accusantium dolor aut fuga Mollit iusto quia distinctio Incididunt unde vel"]	["Soluta minim cumque officia non aut ut laborum Nam corrupti quis labore voluptatem Id quia sed"]	["Dolor in omnis enim qui sint esse a aliquam deleniti aut magna"]	[]	f	2025-07-25 20:18:16	2025-07-25 20:18:16	\N	t
42	27	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	Modi labs sitfvel aliquid occaecat voluptatem aspernatur quam qui sunt ab in sed reprehenderit voluptatem	Omnis sit odit opt doore ofs dorumjhgf est	simple	87	Temporibus corporis dolor dolores veritatis ea error nisi vel esse occaecat impedit ipsam ut fugiat dolorem sint qui reprehenderit	Sint odit nobis sunt inventore a amet anim repudiandae dolorem laboris quos et a	Hic quibusdam qui eaque sapiente sit pariatur Harum non sunt aut perferendis aut commodo quam sint sit dolore cillum	Aut dolorem do non minus labore illum architecto in dignissimos ea quia qui	Fuga Voluptas dolor ut et pariatur Libero accusantium nulla magni duis qui nisi laudantium commodo iure incidunt	Voluptates voluptate corrupti quis voluptas qui atque natus non provident eiusmod consequatur Dolore eius veniam molestiae	Facilis architecto ad consectetur quod tempor corrupti laboriosam optio	Qui ducimus commodi nisi maxime consequatur veniam nihil	\N	Dolorem in proident cumque hic sed corrupti obcaecati incidunt magnam vero velit ex sint nostrum	Suscipit dolores aliquid provident minim dignissimos voluptatem vitae magni itaque	Iste officia tempore ut beatae et repudiandae aliqua Eu distinctio Ut natus laborum ea deserunt duis	Sit sed debitis sapiente aut dolore aliquid culpa ab cum maxime quia in quos placeat deleniti	Velit assumenda accusantium eligendi blanditiis id quam	\N	Dolorum corporis accusamus error cum itaque repellendus Eos atque	Elit rerum veniam quas quis excepteur sed dolore nihil ullam qui	Sint do delectus magnam quisquam labore	Nihil ut ea nihil recusandae Fugiat veritatis dolores nisi veritatis delectus ullamco perferendis do voluptatem Nulla non	Voluptatem non voluptatem fugit ullam reprehenderit numquam commodi debitis facere et asperiores esse et	0.00	0.00	3.00	10.00	78.00	\N	\N	{"montant":68,"devise":"FCFA"}	[]	["Ipsum voluptas sunt consequat Proident harum explicabo Deserunt accusantium dolor aut fuga Mollit iusto quia distinctio Incididunt unde vel"]	["Soluta minim cumque officia non aut ut laborum Nam corrupti quis labore voluptatem Id quia sed"]	["Dolor in omnis enim qui sint esse a aliquam deleniti aut magna"]	[]	f	2025-07-25 20:28:15	2025-07-25 20:28:15	\N	t
43	27	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	Modi labs sitfvel aliquidcaecat voluptatem aspernatur quam qui sunt ab in sed reprehenderit voluptatem	Omnis sit odit opt doore ofs dumjhgf est	simple	87	Temporibus corporis dolor dolores veritatis ea error nisi vel esse occaecat impedit ipsam ut fugiat dolorem sint qui reprehenderit	Sint odit nobis sunt inventore a amet anim repudiandae dolorem laboris quos et a	Hic quibusdam qui eaque sapiente sit pariatur Harum non sunt aut perferendis aut commodo quam sint sit dolore cillum	Aut dolorem do non minus labore illum architecto in dignissimos ea quia qui	Fuga Voluptas dolor ut et pariatur Libero accusantium nulla magni duis qui nisi laudantium commodo iure incidunt	Voluptates voluptate corrupti quis voluptas qui atque natus non provident eiusmod consequatur Dolore eius veniam molestiae	Facilis architecto ad consectetur quod tempor corrupti laboriosam optio	Qui ducimus commodi nisi maxime consequatur veniam nihil	\N	Dolorem in proident cumque hic sed corrupti obcaecati incidunt magnam vero velit ex sint nostrum	Suscipit dolores aliquid provident minim dignissimos voluptatem vitae magni itaque	Iste officia tempore ut beatae et repudiandae aliqua Eu distinctio Ut natus laborum ea deserunt duis	Sit sed debitis sapiente aut dolore aliquid culpa ab cum maxime quia in quos placeat deleniti	Velit assumenda accusantium eligendi blanditiis id quam	\N	Dolorum corporis accusamus error cum itaque repellendus Eos atque	Elit rerum veniam quas quis excepteur sed dolore nihil ullam qui	Sint do delectus magnam quisquam labore	Nihil ut ea nihil recusandae Fugiat veritatis dolores nisi veritatis delectus ullamco perferendis do voluptatem Nulla non	Voluptatem non voluptatem fugit ullam reprehenderit numquam commodi debitis facere et asperiores esse et	0.00	0.00	3.00	10.00	78.00	\N	\N	{"montant":68,"devise":"FCFA"}	[]	["Ipsum voluptas sunt consequat Proident harum explicabo Deserunt accusantium dolor aut fuga Mollit iusto quia distinctio Incididunt unde vel"]	["Soluta minim cumque officia non aut ut laborum Nam corrupti quis labore voluptatem Id quia sed"]	["Dolor in omnis enim qui sint esse a aliquam deleniti aut magna"]	[]	f	2025-07-25 20:28:25	2025-07-25 20:28:25	\N	t
44	27	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	Modi labs sitfvel aliqffuidcaecat voluptatem aspernatur quam qui sunt ab in sed reprehenderit voluptatem	Omnis sit odit opt doore fofs dumjhgf est	simple	87	Temporibus corporis dolor dolores veritatis ea error nisi vel esse occaecat impedit ipsam ut fugiat dolorem sint qui reprehenderit	Sint odit nobis sunt inventore a amet anim repudiandae dolorem laboris quos et a	Hic quibusdam qui eaque sapiente sit pariatur Harum non sunt aut perferendis aut commodo quam sint sit dolore cillum	Aut dolorem do non minus labore illum architecto in dignissimos ea quia qui	Fuga Voluptas dolor ut et pariatur Libero accusantium nulla magni duis qui nisi laudantium commodo iure incidunt	Voluptates voluptate corrupti quis voluptas qui atque natus non provident eiusmod consequatur Dolore eius veniam molestiae	Facilis architecto ad consectetur quod tempor corrupti laboriosam optio	Qui ducimus commodi nisi maxime consequatur veniam nihil	\N	Dolorem in proident cumque hic sed corrupti obcaecati incidunt magnam vero velit ex sint nostrum	Suscipit dolores aliquid provident minim dignissimos voluptatem vitae magni itaque	Iste officia tempore ut beatae et repudiandae aliqua Eu distinctio Ut natus laborum ea deserunt duis	Sit sed debitis sapiente aut dolore aliquid culpa ab cum maxime quia in quos placeat deleniti	Velit assumenda accusantium eligendi blanditiis id quam	\N	Dolorum corporis accusamus error cum itaque repellendus Eos atque	Elit rerum veniam quas quis excepteur sed dolore nihil ullam qui	Sint do delectus magnam quisquam labore	Nihil ut ea nihil recusandae Fugiat veritatis dolores nisi veritatis delectus ullamco perferendis do voluptatem Nulla non	Voluptatem non voluptatem fugit ullam reprehenderit numquam commodi debitis facere et asperiores esse et	0.00	0.00	3.00	10.00	78.00	\N	\N	{"montant":68,"devise":"FCFA"}	[]	["Ipsum voluptas sunt consequat Proident harum explicabo Deserunt accusantium dolor aut fuga Mollit iusto quia distinctio Incididunt unde vel"]	["Soluta minim cumque officia non aut ut laborum Nam corrupti quis labore voluptatem Id quia sed"]	["Dolor in omnis enim qui sint esse a aliquam deleniti aut magna"]	[]	f	2025-07-25 20:28:33	2025-07-25 20:28:33	\N	t
45	27	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	Modi labs sitfvefffl aliqffuidcaecat voluptatem aspernatur quam qui sunt ab in sed reprehenderit voluptatem	Omnis sit odit opt fffff fofs dumjhgf est	simple	87	Temporibus corporis dolor dolores veritatis ea error nisi vel esse occaecat impedit ipsam ut fugiat dolorem sint qui reprehenderit	Sint odit nobis sunt inventore a amet anim repudiandae dolorem laboris quos et a	Hic quibusdam qui eaque sapiente sit pariatur Harum non sunt aut perferendis aut commodo quam sint sit dolore cillum	Aut dolorem do non minus labore illum architecto in dignissimos ea quia qui	Fuga Voluptas dolor ut et pariatur Libero accusantium nulla magni duis qui nisi laudantium commodo iure incidunt	Voluptates voluptate corrupti quis voluptas qui atque natus non provident eiusmod consequatur Dolore eius veniam molestiae	Facilis architecto ad consectetur quod tempor corrupti laboriosam optio	Qui ducimus commodi nisi maxime consequatur veniam nihil	\N	Dolorem in proident cumque hic sed corrupti obcaecati incidunt magnam vero velit ex sint nostrum	Suscipit dolores aliquid provident minim dignissimos voluptatem vitae magni itaque	Iste officia tempore ut beatae et repudiandae aliqua Eu distinctio Ut natus laborum ea deserunt duis	Sit sed debitis sapiente aut dolore aliquid culpa ab cum maxime quia in quos placeat deleniti	Velit assumenda accusantium eligendi blanditiis id quam	\N	Dolorum corporis accusamus error cum itaque repellendus Eos atque	Elit rerum veniam quas quis excepteur sed dolore nihil ullam qui	Sint do delectus magnam quisquam labore	Nihil ut ea nihil recusandae Fugiat veritatis dolores nisi veritatis delectus ullamco perferendis do voluptatem Nulla non	Voluptatem non voluptatem fugit ullam reprehenderit numquam commodi debitis facere et asperiores esse et	0.00	0.00	3.00	10.00	78.00	\N	\N	{"montant":68,"devise":"FCFA"}	[]	["Ipsum voluptas sunt consequat Proident harum explicabo Deserunt accusantium dolor aut fuga Mollit iusto quia distinctio Incididunt unde vel"]	["Soluta minim cumque officia non aut ut laborum Nam corrupti quis labore voluptatem Id quia sed"]	["Dolor in omnis enim qui sint esse a aliquam deleniti aut magna"]	[]	f	2025-07-25 21:16:23	2025-07-25 21:16:23	\N	t
38	27	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	Vero autem quasi quia consectetur mollitia amet alias deleniti	Ea quam deserunt nulla fugiat ad magnam velit nemo	simple	61	Tempor tempore inventore sunt ex asperiores dignissimos voluptatum ad accusantium exercitation ut soluta ab deserunt dolores voluptatum laudantium	Fuga Dolores pariatur Beatae tempora consectetur necessitatibus repudiandae repudiandae nobis voluptate eu assumenda a obcaecati optio impedit tempore	Officia natus quidem molestias facere perferendis tempore ab adipisci delectus	Fugiat deleniti magnam ullam reprehenderit in et id voluptatum id exercitationem voluptatem dolor est irure qui voluptas facilis	Elit aut molestiae facilis molestiae	Elit iusto corporis eius sed ea atque placeat rerum distinctio Ducimus optio eos fugiat dicta libero reprehenderit est	Dolorem suscipit facilis est officiis velit error ut voluptatem quia non illum Nam ex dolor fuga Qui laboris incididunt	Facilis consequatur blanditiis nemo obcaecati magna qui et qui dignissimos nesciunt animi atque dolor	\N	Possimus consequat Expedita aut consectetur anim animi consequatur optio	Eligendi ex facere proident eius autem incididunt dignissimos id esse	Aliquam magnam dolorem mollitia inventore excepteur veniam harum et ad aliquip esse lorem tempor cupidatat laborum iste harum saepe consequatur	Ea id voluptas aut nobis amet labore pariatur Rerum dolores voluptate minima ipsum qui ratione porro laborum Est quo facere	Occaecat ea quam voluptas odio excepturi animi impedit aliquam ad et dicta tempor esse quo aliquip at non earum	\N	Nihil aspernatur eius sit quidem duis qui	Iure nulla alias reprehenderit minim dolores sapiente porro ut inventore quas duis rerum rerum accusantium	Vel maiores voluptatem Ipsum blanditiis enim ad nostrud id est in ea alias dolorem ex sunt dolor nulla vel	Aut omnis repudiandae autem sint ut alias deleniti necessitatibus quaerat qui eum quia provident fugit	Aut assumenda impedit excepturi ut laboriosam	0.00	0.00	7.00	53.00	89.00	\N	\N	{"montant":20,"devise":"FCFA"}	[]	["Aut autem qui sit sit deleniti illum voluptate ut voluptas"]	["Dicta ab quia voluptatum soluta eum"]	["Animi fugiat mollit est fugit expedita sit iure dolorem repudiandae dolorem tempore"]	[]	f	2025-07-25 17:30:52	2025-07-25 21:28:26	2025-07-25 21:28:26	f
46	\N	\N	\N	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	onsequaturdre ftryryhg rerum	\N	simple	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	0.00	0.00	\N	\N	\N	\N	\N	\N	[]	\N	\N	\N	[]	f	2025-07-27 19:16:49	2025-07-28 09:20:36	2025-07-28 09:20:36	f
58	\N	\N	\N	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	Beatae Nam aliquip ullamco aliqua Labore	Voluptas sint eu quasi deserunt proident est su	simple	11	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	0.00	0.00	87.00	62.00	7.00	\N	\N	{"montant":43,"devise":"FCFA"}	[]	[]	[]	[]	[]	f	2025-07-28 11:19:41	2025-07-28 11:20:32	2025-07-28 11:20:32	f
57	\N	\N	\N	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	onsequaturdre sftryryhg rerum	\N	simple	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	0.00	0.00	\N	\N	\N	\N	\N	\N	[]	\N	\N	\N	[]	f	2025-07-28 11:15:49	2025-07-28 11:20:39	2025-07-28 11:20:39	t
59	\N	\N	\N	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	onsequaturdre sftryryhg rerum	\N	simple	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	0.00	0.00	\N	\N	\N	\N	\N	\N	[]	\N	\N	\N	[]	f	2025-07-28 11:20:50	2025-07-28 11:20:50	\N	t
53	\N	\N	\N	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	onsequaturdre ftryryhg rerum	\N	simple	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	0.00	0.00	\N	\N	\N	\N	\N	\N	[]	\N	\N	\N	[]	f	2025-07-28 10:13:06	2025-07-28 11:20:51	2025-07-28 11:20:51	t
60	\N	\N	\N	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	Omnis eius voluptates esse corrupti beatae	Quis blanditiis necessitatibus quasi voluptas laud	simple	75	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	0.00	0.00	84.00	57.00	52.00	\N	\N	{"montant":39,"devise":"FCFA"}	[]	[]	[]	[]	[]	f	2025-07-28 11:21:16	2025-07-28 11:21:31	2025-07-28 11:21:31	f
61	\N	\N	\N	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	Explicabo Nostrum rerum animi veritatis assumenda laborum officia facilis voluptate id unde velit a facilis adipisicing dolores	Non omnis sed autem qui cupidatat aliquid odio ess	simple	49	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	0.00	0.00	79.00	70.00	26.00	\N	\N	{"montant":63,"devise":"FCFA"}	[]	[]	[]	[]	[]	f	2025-07-28 11:21:48	2025-07-28 11:21:48	\N	f
62	27	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	Modi labs sitfvfffl aliqffuidcaecat voluptatem aspernatur quam qui sunt ab in sed reprehenderit voluptatem	Omnis sit odit opt fffff fofs dumjgf est	simple	87	Temporibus corporis dolor dolores veritatis ea error nisi vel esse occaecat impedit ipsam ut fugiat dolorem sint qui reprehenderit	Sint odit nobis sunt inventore a amet anim repudiandae dolorem laboris quos et a	Hic quibusdam qui eaque sapiente sit pariatur Harum non sunt aut perferendis aut commodo quam sint sit dolore cillum	Aut dolorem do non minus labore illum architecto in dignissimos ea quia qui	Fuga Voluptas dolor ut et pariatur Libero accusantium nulla magni duis qui nisi laudantium commodo iure incidunt	Voluptates voluptate corrupti quis voluptas qui atque natus non provident eiusmod consequatur Dolore eius veniam molestiae	Facilis architecto ad consectetur quod tempor corrupti laboriosam optio	Qui ducimus commodi nisi maxime consequatur veniam nihil	\N	Dolorem in proident cumque hic sed corrupti obcaecati incidunt magnam vero velit ex sint nostrum	Suscipit dolores aliquid provident minim dignissimos voluptatem vitae magni itaque	Iste officia tempore ut beatae et repudiandae aliqua Eu distinctio Ut natus laborum ea deserunt duis	Sit sed debitis sapiente aut dolore aliquid culpa ab cum maxime quia in quos placeat deleniti	Velit assumenda accusantium eligendi blanditiis id quam	\N	Dolorum corporis accusamus error cum itaque repellendus Eos atque	Elit rerum veniam quas quis excepteur sed dolore nihil ullam qui	Sint do delectus magnam quisquam labore	Nihil ut ea nihil recusandae Fugiat veritatis dolores nisi veritatis delectus ullamco perferendis do voluptatem Nulla non	Voluptatem non voluptatem fugit ullam reprehenderit numquam commodi debitis facere et asperiores esse et	0.00	0.00	3.00	10.00	78.00	\N	\N	{"montant":68,"devise":"FCFA"}	[]	["Ipsum voluptas sunt consequat Proident harum explicabo Deserunt accusantium dolor aut fuga Mollit iusto quia distinctio Incididunt unde vel"]	["Soluta minim cumque officia non aut ut laborum Nam corrupti quis labore voluptatem Id quia sed"]	["Dolor in omnis enim qui sint esse a aliquam deleniti aut magna"]	[]	f	2025-07-28 11:27:54	2025-07-28 11:27:54	\N	t
63	\N	\N	\N	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	Eaque anim libero iusto in minim amet exercitation est mollit reprehenderit	Voluptatum amet temporibus incididunt cupiditate	simple	92	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	0.00	0.00	34.00	80.00	29.00	\N	\N	{"montant":45,"devise":"FCFA"}	[]	[]	[]	[]	[]	f	2025-07-28 11:35:24	2025-07-28 11:35:24	\N	f
65	\N	\N	\N	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	onsequaturdre um	\N	simple	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	0.00	0.00	\N	\N	\N	\N	\N	\N	[]	\N	\N	\N	[]	f	2025-07-28 11:48:02	2025-07-28 11:48:02	\N	t
64	\N	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	Sint earum ut dignissimos quae ad nulla quo eum duis pariatur Magna et sed voluptas ad	Deserunt voluptatem Veniam itaque consequatur I	simple	71	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	0.00	0.00	73.00	34.00	17.00	\N	\N	{"montant":46,"devise":"FCFA"}	[]	[]	[]	[]	[]	f	2025-07-28 11:45:59	2025-07-28 11:48:13	2025-07-28 11:48:13	f
72	\N	\N	\N	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	onsequatugfrvhgh dre um	\N	simple	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	0.00	0.00	\N	\N	\N	\N	\N	\N	[]	\N	\N	\N	[]	f	2025-07-29 06:15:41	2025-07-29 06:15:41	\N	f
73	27	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	gfj	dsfsd	simple	21	yuy	uyy	yuu	yuuuy	yuyu	rty	tyrty	yrtr	\N	ytrt	tryr	ytr	ytrtrrty	yrytr	\N	rtyr	uii	uiui	uy	rytr	0.00	0.00	22.00	22.00	22.00	\N	\N	{"montant":22,"devise":"FCFA"}	[]	["iuu"]	["uuy"]	["yyu"]	[]	f	2025-07-29 08:44:19	2025-07-29 08:44:19	\N	f
68	\N	\N	\N	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	onsequatugfrdre um	\N	simple	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	0.00	0.00	\N	\N	\N	\N	\N	\N	[]	\N	\N	\N	[]	f	2025-07-28 11:56:01	2025-07-28 11:56:01	\N	f
66	\N	\N	\N	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	Fuga Nihil enim non non quia laboriosam ut deserunt ratione	Sint est nesciunt rerum fugiat inventore velit la	simple	[8]	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	0.00	0.00	35.00	5.00	95.00	\N	\N	{"montant":60,"devise":"FCFA"}	[]	[]	[]	[]	[]	f	2025-07-28 11:50:07	2025-07-28 12:27:40	2025-07-28 12:27:40	f
67	\N	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	medoski	Aut reprehenderit nemo voluptas nisi occaecat acc	simple	[35]	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	0.00	0.00	21.00	49.00	64.00	\N	\N	{"montant":42,"devise":"FCFA"}	[]	[]	[]	[]	[]	f	2025-07-28 11:52:45	2025-07-28 12:27:45	2025-07-28 12:27:45	f
70	\N	\N	\N	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	onsequatugfrvhgh dre um	\N	simple	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	0.00	0.00	\N	\N	\N	\N	\N	\N	[]	\N	\N	\N	[]	f	2025-07-28 13:51:28	2025-07-28 14:48:00	2025-07-28 14:48:00	f
74	\N	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	tretruiuiuiui	rtrtr	simple	[23]	\N	\N	\N	\N	\N	hjh	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	jk	\N	0.00	0.00	12.00	12.00	13.00	\N	\N	{"montant":12,"devise":"FCFA"}	[]	[]	[]	[]	[]	f	2025-07-29 09:01:15	2025-07-29 09:02:04	\N	f
69	\N	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	Perspiciatis numuam dolre voluptates dolore quo ullam magnam blanditiis sapiente sunt aut deleniti id quibusdam esse cillum	Sunt quam obcaecati velit culpa magni libero	simple	[7]	\N	\N	\N	\N	\N	Architecto neque non provident aliquam ut sint et	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	Velit blanditiis quia molestiae consequatur Sunt sunt aute aut quisquam et tempore aut doloribus	\N	0.00	0.00	7.00	70.00	85.00	\N	\N	{"montant":3,"devise":"FCFA"}	[]	[]	[]	[]	[]	f	2025-07-28 13:34:21	2025-07-28 15:05:47	2025-07-28 15:05:47	f
71	\N	\N	1	\N	\N	\N	\N	f	00_brouillon	identification	redaction	\N	Molestias rem dicta neschhhiunt ipsum ipsum dolor itaque vel laboris repellendus Minus consequatur officia	Eos id aute aute enim aliquip	simple	11	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	0.00	0.00	84.00	40.00	72.00	\N	\N	{"montant":25,"devise":"FCFA"}	[]	[]	[]	[]	[]	f	2025-07-28 15:06:06	2025-07-28 15:06:06	\N	f
\.


--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- Data for Name: lieux_intervention_projets; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.lieux_intervention_projets (id, "departementId", "communeId", "arrondissementId", "villageId", projetable_type, projetable_id, created_at, updated_at, deleted_at) FROM stdin;
1	1	1	1	2	App\\Models\\IdeeProjet	74	2025-07-29 09:01:15	2025-07-29 09:01:15	\N
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000001_create_cache_table	1
2	0001_01_01_000002_create_jobs_table	1
3	2025_07_15_095035_create_organisations_table	1
4	2025_07_15_095100_create_personnes_table	1
5	2025_07_16_145609_create_roles_table	1
6	2025_07_16_171208_create_permissions_table	1
7	2025_07_16_172105_create_role_permissions_table	1
8	2025_07_16_182150_create_users_table	1
9	2025_07_16_185902_create_user_roles_table	1
10	2025_07_16_194907_create_departements_table	1
11	2025_07_16_194922_create_communes_table	1
12	2025_07_16_194950_create_arrondissements_table	1
13	2025_07_16_195000_create_villages_table	1
14	2025_07_16_195033_create_secteurs_table	1
15	2025_07_16_195102_create_types_intervention_table	1
16	2025_07_16_195119_create_financements_table	1
17	2025_07_16_195128_create_categories_projet_table	1
18	2025_07_16_195723_create_odds_table	1
19	2025_07_16_200011_create_cibles_table	1
20	2025_07_16_201537_create_types_programme_table	1
21	2025_07_16_203802_create_composants_programme_table	1
22	2025_07_16_204400_create_idees_projet_table	1
23	2025_07_16_205358_create_sources_financement_projets_table	1
24	2025_07_16_205530_create_types_intervention_projets_table	1
25	2025_07_16_205631_create_lieux_intervention_projets_table	1
26	2025_07_16_210203_create_odds_projets_table	1
27	2025_07_16_210415_create_cibles_projets_table	1
28	2025_07_16_210605_create_composants_projet_table	1
29	2025_07_16_210834_create_projets_table	1
30	2025_07_16_211829_create_workflows_table	1
31	2025_07_17_101005_create_decisions_table	1
32	2025_07_17_152120_create_commentaires_table	1
33	2025_07_17_152352_create_statuts_table	1
34	2025_07_17_152550_create_categories_document_table	1
35	2025_07_17_154246_create_track_infos_table	1
36	2025_07_17_161801_create_documents_table	1
43	2025_07_17_163441_create_champs_sections_table	2
44	2025_07_17_164850_create_champs_table	3
47	2025_07_18_062351_modify_financements_table_make_financement_id_nullable	3
48	2025_07_18_124511_remove_code_from_composants_programme_table	3
51	2025_07_21_191029_add_column_settings_to_users_table	4
52	2025_07_21_210604_add_column_champ_standard_to_champs_table	4
199	2025_07_28_191443_add_columns_to_users_table	10
200	2025_07_28_193041_create_dpaf_table	10
201	2025_07_28_193211_create_dgpd_table	10
202	2025_07_28_193850_create_groupes_utilisateur_table	10
203	2025_07_28_194953_create_groupe_utilisateur_users_table	10
204	2025_07_28_204131_create_groupe_utilisateur_roles_table	10
205	2025_07_29_031314_create_categories_critere_table	10
206	2025_07_29_043325_create_criteres_table	10
207	2025_07_29_043339_create_notations_table	10
208	2025_07_29_043356_create_evaluation_criteres_table	10
209	2025_07_17_183154_create_evaluations_table	11
210	2025_07_17_183252_create_evaluation_champs_table	11
104	2025_07_17_172225_create_champs_projet_table	5
105	2025_07_22_050210_add_keycloak_id_to_users_table	5
106	2025_07_23_105816_update_columns_idees_projet_table	5
107	2025_07_24_193355_create_personal_access_tokens_table	6
108	2025_07_25_145736_create_oauth_auth_codes_table	7
117	2025_07_25_145737_create_oauth_access_tokens_table	8
122	2025_07_25_151721_create_oauth_auth_codes_table	9
123	2025_07_25_151722_create_oauth_access_tokens_table	9
124	2025_07_25_151723_create_oauth_refresh_tokens_table	9
125	2025_07_25_151724_create_oauth_clients_table	9
126	2025_07_25_151725_create_oauth_device_codes_table	9
\.


--
-- Data for Name: notations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.notations (id, libelle, valeur, commentaire, critere_id, categorie_critere_id, created_at, updated_at, deleted_at) FROM stdin;
1	Négatif	-3	Le projet aggrave les impacts climatiques en augmentant significativement les émissions de gaz à effet de serre (GES) ou en amplifiant les vulnérabilités environnementales (déforestation, destruction des écosystèmes)	1	1	2025-07-29 09:01:59	2025-07-29 09:01:59	\N
2	Neutre	0	Le projet n'a aucun effet significatif sur le climat. Il n'entraîne ni réduction ni augmentation des émissions ou des risques environnementaux.	1	1	2025-07-29 09:01:59	2025-07-29 09:01:59	\N
3	Moyenne	3	Le projet contribue modérément à l'atténuation des impacts climatiques, par exemple en réduisant les émissions de GES ou en améliorant l'efficacité énergétique de manière mesurable.	1	1	2025-07-29 09:01:59	2025-07-29 09:01:59	\N
4	Élevée	5	Le projet a un impact très positif sur le climat, notamment en réduisant fortement les émissions, en favorisant les énergies renouvelables, ou en améliorant la résilience climatique (adaptation aux risques).	1	1	2025-07-29 09:01:59	2025-07-29 09:01:59	\N
5	Négatif	-3	Le projet aggrave les impacts climatiques en augmentant significativement les émissions de gaz à effet de serre (GES) ou en amplifiant les vulnérabilités environnementales (déforestation, destruction des écosystèmes)	2	1	2025-07-29 09:01:59	2025-07-29 09:01:59	\N
6	Neutre	0	Le projet n'a aucun effet significatif sur le climat. Il n'entraîne ni réduction ni augmentation des émissions ou des risques environnementaux.	2	1	2025-07-29 09:01:59	2025-07-29 09:01:59	\N
7	Moyenne	3	Le projet contribue modérément à l'atténuation des impacts climatiques, par exemple en réduisant les émissions de GES ou en améliorant l'efficacité énergétique de manière mesurable.	2	1	2025-07-29 09:01:59	2025-07-29 09:01:59	\N
8	Élevée	5	Le projet a un impact très positif sur le climat, notamment en réduisant fortement les émissions, en favorisant les énergies renouvelables, ou en améliorant la résilience climatique (adaptation aux risques).	2	1	2025-07-29 09:01:59	2025-07-29 09:01:59	\N
9	Neutre	0	Le projet n'a aucune contribution notable, positive ou négative, aux objectifs climatiques des CDN. Ses activités sont neutres en matière de climat, n'ayant ni effet direct sur les émissions, ni impact sur la résilience climatique.	3	1	2025-07-29 09:01:59	2025-07-29 09:01:59	\N
10	Faible	1	Le projet a une faible contribution aux objectifs climatiques. Il intègre seulement des actions marginales en matière de réduction des émissions de GES ou d'adaptation, sans effet substantiel.	3	1	2025-07-29 09:01:59	2025-07-29 09:01:59	\N
11	Moyenne	2	Le projet contribue modérément à l'atténuation des impacts climatiques, par exemple en réduisant les émissions de GES ou en améliorant l'efficacité énergétique de manière mesurable.	3	1	2025-07-29 09:01:59	2025-07-29 09:01:59	\N
12	Élevée	3	Le projet contribue significativement aux objectifs de réduction des émissions ou d'adaptation climatique définis dans les CDN du Bénin.	3	1	2025-07-29 09:01:59	2025-07-29 09:01:59	\N
13	Neutre	0	Le projet n'a aucun effet significatif sur le changement climatique, ni en termes de réduction des émissions ni d'adaptation. Il n'entraîne ni amélioration ni dégradation des conditions climatiques actuelles.	4	1	2025-07-29 09:01:59	2025-07-29 09:01:59	\N
14	Faible	1	Le projet intègre des mesures climatiques, mais celles-ci sont marginales ou limitées dans leur portée. L'impact sur le long terme ou à grande échelle reste faible.	4	1	2025-07-29 09:01:59	2025-07-29 09:01:59	\N
15	Moyenne	2	Le projet contribue de manière significative à la réduction des émissions ou à l'adaptation climatique, mais ses effets sont limités à un secteur ou à une région spécifique. Le changement est substantiel mais pas entièrement transformateur.	4	1	2025-07-29 09:01:59	2025-07-29 09:01:59	\N
16	Élevée	3	Le projet entraîne un changement profond et durable vers une économie faible en carbone ou résiliente au climat. Il transforme radicalement les pratiques existantes et peut servir de modèle pour d'autres initiatives similaires.	4	1	2025-07-29 09:01:59	2025-07-29 09:01:59	\N
17	Négatif	-3	Le projet aggrave les impacts climatiques en augmentant significativement les émissions de gaz à effet de serre (GES) ou en amplifiant les vulnérabilités environnementales (déforestation, destruction des écosystèmes)	\N	7	2025-07-29 11:59:57	2025-07-29 11:59:57	\N
18	Neutre	0	Le projet n'a aucun effet significatif sur le climat. Il n'entraîne ni réduction ni augmentation des émissions ou des risques environnementaux.	\N	7	2025-07-29 11:59:57	2025-07-29 11:59:57	\N
19	Moyenne	3	Le projet contribue modérément à l'atténuation des impacts climatiques, par exemple en réduisant les émissions de GES ou en améliorant l'efficacité énergétique de manière mesurable.	\N	7	2025-07-29 11:59:57	2025-07-29 11:59:57	\N
20	Élevée	5	Le projet a un impact très positif sur le climat, notamment en réduisant fortement les émissions, en favorisant les énergies renouvelables, ou en améliorant la résilience climatique (adaptation aux risques).	\N	7	2025-07-29 11:59:57	2025-07-29 11:59:57	\N
\.


--
-- Data for Name: oauth_access_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.oauth_access_tokens (id, user_id, client_id, name, scopes, revoked, created_at, updated_at, expires_at) FROM stdin;
31e812af5d519f6ff6487203143452ac97b607e70b7bb6398f42b5966e09903d25e2a3dc23910d95	1	01984229-0484-714f-a279-d00ad0ca9b6c	Bip-Token	[]	f	2025-07-28 14:54:19	2025-07-28 14:54:19	2025-08-12 14:54:19
ce65ccd71a440d0de0b31a485dbf46e4f603d60f814086626706ff3cf815038f5d27bfca862c66a7	1	01984229-0484-714f-a279-d00ad0ca9b6c	Bip-Token	[]	f	2025-07-28 19:05:36	2025-07-28 19:05:36	2025-08-12 19:05:36
124a3dea64a93095334b17fc667450d67f5c6cfe779a85c6817adced0aeb0e1d7adb1a27da8a1b43	1	01984229-0484-714f-a279-d00ad0ca9b6c	Bip-Token	[]	f	2025-07-28 19:05:43	2025-07-28 19:05:43	2025-08-12 19:05:43
0d3787ab194bbecd44869546dee2cb8493a2cb418350c3164b2d0e2e86fcb8ad3684819cfab0c5a6	1	01984229-0484-714f-a279-d00ad0ca9b6c	Bip-Token	[]	f	2025-07-28 23:40:17	2025-07-28 23:40:17	2025-08-12 23:40:17
24cd3077ce903345533470b7836371465da5811a3526e03835f9d57367cd54269e29e5df565bdbed	1	01984229-0484-714f-a279-d00ad0ca9b6c	Bip-Token	[]	f	2025-07-28 23:42:56	2025-07-28 23:42:56	2025-08-12 23:42:56
9683e0e62f891a6410fc7b237410045065ff40e9b0c9219d7dff0280ec0426e60b5c9eda24ddb8d8	1	01984229-0484-714f-a279-d00ad0ca9b6c	Bip-Token	[]	f	2025-07-29 01:39:11	2025-07-29 01:39:11	2025-08-13 01:39:11
d9615a20fe7c21504f24e8f2c62a88980e42df6708701aeb02c13da15c79272a575171a826ed3372	12	01984229-0484-714f-a279-d00ad0ca9b6c	Bip-Token	[]	f	2025-07-29 02:41:07	2025-07-29 02:41:07	2025-08-13 02:41:07
e8ad8add2c5565a9d419c32a97ef08afbb42d9938f27de212cf5dd78f3d1edd9238a95ee225684ec	12	01984229-0484-714f-a279-d00ad0ca9b6c	Bip-Token	[]	f	2025-07-29 06:36:47	2025-07-29 06:36:47	2025-08-13 06:36:47
e8063be59eff99b9cc0609c56937d76e76a12cbb66835ad758c8469ba2a6f4d006aa1403934467ea	12	01984229-0484-714f-a279-d00ad0ca9b6c	Bip-Token	[]	f	2025-07-29 10:31:07	2025-07-29 10:31:07	2025-08-13 10:31:07
7a290cae9331f6251a7570bc50d5cb978d4d3e9f572367893abfc418e9ff88d7f81098db551c2e5e	15	01984229-0484-714f-a279-d00ad0ca9b6c	Bip-Token	[]	f	2025-07-29 11:00:35	2025-07-29 11:00:35	2025-08-13 11:00:35
b5468852127a8f0426332f55654cb190ec14fca376f3741eaa9a549781227db7e609842f2bbb6742	15	01984229-0484-714f-a279-d00ad0ca9b6c	Bip-Token	[]	f	2025-07-29 11:04:46	2025-07-29 11:04:46	2025-08-13 11:04:46
\.


--
-- Data for Name: oauth_auth_codes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.oauth_auth_codes (id, user_id, client_id, scopes, revoked, expires_at) FROM stdin;
\.


--
-- Data for Name: oauth_clients; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.oauth_clients (id, owner_type, owner_id, name, secret, provider, redirect_uris, grant_types, revoked, created_at, updated_at) FROM stdin;
01984229-0484-714f-a279-d00ad0ca9b6c	\N	\N	BIP	$2y$12$RfsxnOWwyq7R12g/vbmao.BQDM115BDfs4z.lUnPsy.MI8h8MPVNC	users	[]	["personal_access"]	f	2025-07-25 15:17:21	2025-07-25 15:17:21
0198422a-90d9-7109-844b-3a65b9925994	\N	\N	BIP	$2y$12$jf8nmXVBR1UlLi3.EBwFOuMThfw4z6Ehz0hJjYdz/wFkvK7O9oDj6	users	[]	["password","refresh_token"]	f	2025-07-25 15:19:02	2025-07-25 15:19:02
01985183-d893-7100-a59d-2e3d727c23a8	\N	\N	BIP	$2y$12$dhqqyJV.6/osfAR.yRalruJjLpK7spV.hREIoc65Q1N1SqaceWEcS	users	[]	["password","refresh_token"]	f	2025-07-28 14:50:51	2025-07-28 14:50:51
\.


--
-- Data for Name: oauth_device_codes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.oauth_device_codes (id, user_id, client_id, user_code, scopes, revoked, user_approved_at, last_polled_at, expires_at) FROM stdin;
\.


--
-- Data for Name: oauth_refresh_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.oauth_refresh_tokens (id, access_token_id, revoked, expires_at) FROM stdin;
de1b9d0014b15cdc6eef0b4f5f6834db3b748f2e826b47269305420ade6e0aaef21a6302be47c21f	959091b76beed060fe50ffd35a1a175f30e519a348b11de96c46b86679c9bb7b9c6bf4eda548af44	f	2025-07-25 18:58:52
0ee2b5040c7df44d9bfe2c87d499fd83e7e20ed8b877869fff812d79c5594cd079f3383384cdf913	022e4d1da877c3732a4b0ae9186867ebf5266d9f037ebb075c79f5e1ab595347763086df421e5546	f	2025-07-25 18:59:17
94fdd78dad25309dd62fd83e4fbb1781931cc0f97072f7371c42fb8c81d9eb3b3f94d445a93515df	ec97833553eb3dff40330f15a35bc8a2bcde486bbc2cc2e7dc0a7ea51039d32a786527e5bd27884a	f	2025-07-25 19:01:27
24f30590daf80ac50fc42e1054dea899088d11391e50de8e80cd6c46d3f0510e4830c25dbf852dc1	832939bf419add919f9810feae212b07aa4a062495ead4dff91950167da5cd7825fb442bec1e49d1	f	2025-07-25 19:01:46
5609a65c3629397573086177c733e904908d7aa2698b5bc23190d9d58cf58b19f388c5f9fd6b6aa4	a0f0a00396be7bf38c555d51fb8c6c679bd862a233e3e6396b6d2fe9df51170601e1af290ea53d09	f	2025-07-25 19:04:03
feeb4a2f2ef8b4691e7a5604083cb57ea537a9f1b221ed1cf6c98560ef199ccac81a80d80bb69690	0f098a13692f9e15283bf132fca6d912b5110adb686f0706e7aa63c561e24f932f2cfa0010343988	f	2025-07-25 19:05:20
39241f391c863c74ef5d70d58497fa4584d3191b8f6c22d7c8003bda97bb007555c2f059e371025a	98bd6d9b24c8b8ce1b5c7d988cae18105298fe3a7aec6a8554fe0f15371f43be2108e65f441e4ee2	f	2025-07-25 19:05:59
37fd6be2222a66c2b735c2b6866d02e152a22c3258aa1d01a35359f75f4b9ebe41f942222e202e24	78d5086feba7cc1c921a07ccd7eb2e9b456cfaef510e33c8072a2b3134286698d8a1de5a637383b6	f	2025-07-25 21:27:51
a7b15589f2263c825f522145496827055d1e5dbd3d834d42bb07b4aa70fcb77b0679bce3a5f25994	62682c909d7a39a249fb47f1fe2a009ff1fa0c830f723eb90be1a3ec9f84a01c9a4cb21cd274f6ec	f	2025-07-25 21:28:32
5eaa4c266bc8ec636f2c8903479c75515ce5cdf15fe062dc7d0c6a1165467fd1b6fbe4440c2e3325	80e69ce791623f03c8f61a9838a96d357cd1433e9f57bac9c0dcb380d791b11c90c4944c00306b59	f	2025-07-25 21:50:11
40cec60b1a63c7a2b493b70b5311027e79b59f4e1c733f96531ca38d8246f1c78c7304e491c01803	fec864ef31d73c2ff31930aa81d8621150b27ba09077eb54d96408075ec3d55f3db39568787a8db4	f	2025-07-25 21:50:28
cbaeb668316eb720a7f0d1efb5a711b9035a11292bfcc9aa012fa15ab23fd8e2f9ac78d3eb12b854	8c140026d4f725b70f93b0b81456960d4ecdb3b636fba7fa45d8d686e1d35188b4772afa70fc9d46	f	2025-07-25 21:52:01
0766ac712f51524d1181393324f0adfb0ebc0b4ee7df6ccdf6307f0e2769ca12841753ae1c27feea	43099279574c54998724fa4f98bd4d0253dbd4f2c9ee9ee443defdf96e93c1f0eaf2abb1cd2c98c1	f	2025-07-25 21:52:30
\.


--
-- Data for Name: odds; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.odds (id, odd, slug, created_at, updated_at, deleted_at) FROM stdin;
5	1752813478::Objectif de developpement durable	1752813478::objectif-de-developpement-durable	2025-07-18 04:32:04	2025-07-18 04:37:58	2025-07-18 04:37:58
9	new odd	new-odd	2025-07-21 15:30:49	2025-07-21 15:30:49	\N
10	new  odd	new--odd	2025-07-21 15:59:55	2025-07-21 15:59:55	\N
8	1753114728::Objectif de  developpement durable	1753114728::objectif-de--developpement-durable	2025-07-21 15:28:12	2025-07-21 16:18:48	2025-07-21 16:18:48
7	i	i	2025-07-18 08:53:55	2025-07-21 16:22:18	\N
11	n ew odd	n-ew-odd	2025-07-21 18:15:42	2025-07-21 18:15:42	\N
12	new   odd	new---odd	2025-07-21 18:15:52	2025-07-21 18:15:52	\N
6	1753121837::M	1753121837::m	2025-07-18 04:42:46	2025-07-21 18:17:17	2025-07-21 18:17:17
\.


--
-- Data for Name: odds_projets; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.odds_projets (id, "oddId", projetable_type, projetable_id, created_at, updated_at, deleted_at) FROM stdin;
107	9	App\\Models\\IdeeProjet	173	2025-07-24 10:43:47	2025-07-24 10:43:47	\N
7	9	App\\Models\\IdeeProjet	42	2025-07-24 04:30:05	2025-07-24 04:30:05	\N
8	9	App\\Models\\IdeeProjet	43	2025-07-24 04:33:45	2025-07-24 04:33:45	\N
9	9	App\\Models\\IdeeProjet	44	2025-07-24 04:48:42	2025-07-24 04:48:42	\N
10	9	App\\Models\\IdeeProjet	45	2025-07-24 04:49:58	2025-07-24 04:49:58	\N
11	9	App\\Models\\IdeeProjet	46	2025-07-24 04:52:30	2025-07-24 04:52:30	\N
12	9	App\\Models\\IdeeProjet	48	2025-07-24 04:54:50	2025-07-24 04:54:50	\N
13	9	App\\Models\\IdeeProjet	49	2025-07-24 05:12:13	2025-07-24 05:12:13	\N
109	9	App\\Models\\IdeeProjet	175	2025-07-24 10:56:24	2025-07-24 10:56:24	\N
111	9	App\\Models\\IdeeProjet	7	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
16	9	App\\Models\\IdeeProjet	52	2025-07-24 05:28:14	2025-07-24 05:28:14	\N
17	9	App\\Models\\IdeeProjet	53	2025-07-24 05:54:42	2025-07-24 05:54:42	\N
18	9	App\\Models\\IdeeProjet	54	2025-07-24 06:05:03	2025-07-24 06:05:03	\N
19	9	App\\Models\\IdeeProjet	55	2025-07-24 06:19:59	2025-07-24 06:19:59	\N
20	9	App\\Models\\IdeeProjet	56	2025-07-24 06:39:12	2025-07-24 06:39:12	\N
21	9	App\\Models\\IdeeProjet	57	2025-07-24 06:40:45	2025-07-24 06:40:45	\N
24	9	App\\Models\\IdeeProjet	65	2025-07-24 08:05:09	2025-07-24 08:05:09	\N
26	9	App\\Models\\IdeeProjet	68	2025-07-24 08:11:40	2025-07-24 08:11:40	\N
27	9	App\\Models\\IdeeProjet	70	2025-07-24 08:12:30	2025-07-24 08:12:30	\N
29	9	App\\Models\\IdeeProjet	72	2025-07-24 08:12:45	2025-07-24 08:12:45	\N
32	9	App\\Models\\IdeeProjet	75	2025-07-24 08:13:53	2025-07-24 08:13:53	\N
33	9	App\\Models\\IdeeProjet	76	2025-07-24 08:14:12	2025-07-24 08:14:12	\N
34	9	App\\Models\\IdeeProjet	82	2025-07-24 08:28:24	2025-07-24 08:28:24	\N
35	9	App\\Models\\IdeeProjet	88	2025-07-24 08:32:12	2025-07-24 08:32:12	\N
36	9	App\\Models\\IdeeProjet	89	2025-07-24 08:33:27	2025-07-24 08:33:27	\N
37	9	App\\Models\\IdeeProjet	91	2025-07-24 08:34:15	2025-07-24 08:34:15	\N
38	9	App\\Models\\IdeeProjet	92	2025-07-24 08:36:09	2025-07-24 08:36:09	\N
39	9	App\\Models\\IdeeProjet	93	2025-07-24 08:39:19	2025-07-24 08:39:19	\N
40	7	App\\Models\\IdeeProjet	94	2025-07-24 08:42:19	2025-07-24 08:42:19	\N
41	9	App\\Models\\IdeeProjet	95	2025-07-24 08:53:46	2025-07-24 08:53:46	\N
42	9	App\\Models\\IdeeProjet	96	2025-07-24 08:55:15	2025-07-24 08:55:15	\N
43	9	App\\Models\\IdeeProjet	97	2025-07-24 08:55:29	2025-07-24 08:55:29	\N
115	10	App\\Models\\IdeeProjet	13	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
117	11	App\\Models\\IdeeProjet	16	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
118	12	App\\Models\\IdeeProjet	17	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
120	9	App\\Models\\IdeeProjet	20	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
126	9	App\\Models\\IdeeProjet	27	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
128	9	App\\Models\\IdeeProjet	32	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
130	10	App\\Models\\IdeeProjet	34	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
132	9	App\\Models\\IdeeProjet	36	2025-07-25 16:51:49	2025-07-25 16:51:49	\N
134	12	App\\Models\\IdeeProjet	38	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
136	9	App\\Models\\IdeeProjet	40	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
137	9	App\\Models\\IdeeProjet	62	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
81	9	App\\Models\\IdeeProjet	135	2025-07-24 09:28:55	2025-07-24 09:28:55	\N
82	9	App\\Models\\IdeeProjet	136	2025-07-24 09:30:40	2025-07-24 09:30:40	\N
85	9	App\\Models\\IdeeProjet	139	2025-07-24 09:36:25	2025-07-24 09:36:25	\N
86	9	App\\Models\\IdeeProjet	140	2025-07-24 09:36:40	2025-07-24 09:36:40	\N
87	9	App\\Models\\IdeeProjet	141	2025-07-24 09:37:49	2025-07-24 09:37:49	\N
88	9	App\\Models\\IdeeProjet	144	2025-07-24 09:42:42	2025-07-24 09:42:42	\N
89	9	App\\Models\\IdeeProjet	145	2025-07-24 09:43:05	2025-07-24 09:43:05	\N
90	9	App\\Models\\IdeeProjet	146	2025-07-24 09:44:19	2025-07-24 09:44:19	\N
91	9	App\\Models\\IdeeProjet	147	2025-07-24 09:46:02	2025-07-24 09:46:02	\N
92	9	App\\Models\\IdeeProjet	148	2025-07-24 09:46:55	2025-07-24 09:46:55	\N
106	9	App\\Models\\IdeeProjet	172	2025-07-24 10:42:51	2025-07-24 10:42:51	\N
108	9	App\\Models\\IdeeProjet	174	2025-07-24 10:55:38	2025-07-24 10:55:38	\N
110	9	App\\Models\\IdeeProjet	176	2025-07-24 11:00:39	2025-07-24 11:00:39	\N
112	9	App\\Models\\IdeeProjet	8	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
116	12	App\\Models\\IdeeProjet	15	2025-07-24 20:47:49	2025-07-24 20:47:49	\N
119	9	App\\Models\\IdeeProjet	19	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
121	9	App\\Models\\IdeeProjet	21	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
125	9	App\\Models\\IdeeProjet	26	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
127	10	App\\Models\\IdeeProjet	31	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
129	7	App\\Models\\IdeeProjet	33	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
131	10	App\\Models\\IdeeProjet	35	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
133	9	App\\Models\\IdeeProjet	37	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
135	9	App\\Models\\IdeeProjet	39	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
140	11	App\\Models\\IdeeProjet	73	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
\.


--
-- Data for Name: organisations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.organisations (id, nom, slug, description, type, "parentId", created_at, updated_at, deleted_at) FROM stdin;
7	Ministere Sectoriel	ministere-sectoriel	\N	ministere	\N	2025-07-18 06:16:53	2025-07-18 06:16:53	\N
1	1752821338::Ministere Sectoriel 2	1752821338::ministere-sectoriel-2	Ministère en charge de la planification nationale	ministere	\N	2025-07-17 19:42:27	2025-07-18 06:48:58	2025-07-18 06:48:58
2	Ministere Sectoriel 2	ministere-sectoriel-2	Ministère des finances publiques	ministere	\N	2025-07-17 19:42:27	2025-07-21 11:20:33	\N
8	1753098069::Ministere Sectorieel	1753098069::ministere-sectorieel	\N	ministere	\N	2025-07-21 11:40:55	2025-07-21 11:41:09	2025-07-21 11:41:09
9	Ministere Sectorieel	ministere-sectorieel	\N	ministere	\N	2025-07-21 12:12:07	2025-07-21 12:12:07	\N
10	Medoski	medoski	\N	ministere	\N	2025-07-21 13:26:55	2025-07-21 13:26:55	\N
3	1753709845::DPAF - Ministère du Plan	1753709845::dpaf-ministere-plan	Direction de la Planification et Administration Financière - Ministère du Plan	dpaf	2	2025-07-17 19:42:27	2025-07-28 13:37:25	2025-07-28 13:37:25
4	1753709853::Direction Générale de la Planification et du Développement (DGPD)	1753709853::dgpd	Direction générale de la planification et du développement	dgpd	2	2025-07-17 19:42:27	2025-07-28 13:37:33	2025-07-28 13:37:33
13	Ministere du numerique	ministere-du-numerique	\N	ministere	\N	2025-07-29 02:43:06	2025-07-29 02:43:06	\N
14	Agence des Systèmes d\\'Information et du Numérique.	asin	\N	etatique	13	2025-07-29 02:45:04	2025-07-29 04:18:09	\N
12	1753786337::Trelsjkd	1753786337::trelsjkd	\N	dpaf	2	2025-07-24 13:14:57	2025-07-29 10:52:17	2025-07-29 10:52:17
11	1753786351::Test	1753786351::test	\N	dgb	9	2025-07-23 22:53:48	2025-07-29 10:52:31	2025-07-29 10:52:31
5	1753786371::Direction Générale du Budget (DGB)	1753786371::dgb	Direction générale du budget	dgb	2	2025-07-17 19:42:27	2025-07-29 10:52:51	2025-07-29 10:52:51
15	Agence des Systèmes d\\'Information et du Numérique	agence-des-systèmes-d'information-et-du-numérique	\N	etatique	13	2025-07-29 11:52:30	2025-07-29 11:52:30	\N
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: permissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.permissions (id, nom, slug, description, created_at, updated_at, deleted_at) FROM stdin;
1	Gerer utilisateurs	gerer-utilisateurs	Permission pour gerer utilisateurs	2025-07-24 19:13:08	2025-07-24 19:13:08	\N
2	Voir utilisateurs	voir-utilisateurs	Permission pour voir utilisateurs	2025-07-24 19:13:08	2025-07-24 19:13:08	\N
3	Creer utilisateur	creer-utilisateur	Permission pour creer utilisateur	2025-07-24 19:13:08	2025-07-24 19:13:08	\N
4	Modifier utilisateur	modifier-utilisateur	Permission pour modifier utilisateur	2025-07-24 19:13:08	2025-07-24 19:13:08	\N
5	Supprimer utilisateur	supprimer-utilisateur	Permission pour supprimer utilisateur	2025-07-24 19:13:08	2025-07-24 19:13:08	\N
6	Gerer roles	gerer-roles	Permission pour gerer roles	2025-07-24 19:13:08	2025-07-24 19:13:08	\N
7	Voir roles	voir-roles	Permission pour voir roles	2025-07-24 19:13:08	2025-07-24 19:13:08	\N
8	Creer role	creer-role	Permission pour creer role	2025-07-24 19:13:08	2025-07-24 19:13:08	\N
9	Modifier role	modifier-role	Permission pour modifier role	2025-07-24 19:13:08	2025-07-24 19:13:08	\N
10	Supprimer role	supprimer-role	Permission pour supprimer role	2025-07-24 19:13:08	2025-07-24 19:13:08	\N
11	Assigner permissions	assigner-permissions	Permission pour assigner permissions	2025-07-24 19:13:08	2025-07-24 19:13:08	\N
12	Gerer odds	gerer-odds	Permission pour gerer odds	2025-07-24 19:13:08	2025-07-24 19:13:08	\N
13	Voir odds	voir-odds	Permission pour voir odds	2025-07-24 19:13:08	2025-07-24 19:13:08	\N
14	Creer odd	creer-odd	Permission pour creer odd	2025-07-24 19:13:08	2025-07-24 19:13:08	\N
15	Modifier odd	modifier-odd	Permission pour modifier odd	2025-07-24 19:13:08	2025-07-24 19:13:08	\N
16	Supprimer odd	supprimer-odd	Permission pour supprimer odd	2025-07-24 19:13:08	2025-07-24 19:13:08	\N
17	Gerer cibles	gerer-cibles	Permission pour gerer cibles	2025-07-24 19:13:08	2025-07-24 19:13:08	\N
18	Voir cibles	voir-cibles	Permission pour voir cibles	2025-07-24 19:13:08	2025-07-24 19:13:08	\N
19	Creer cible	creer-cible	Permission pour creer cible	2025-07-24 19:13:08	2025-07-24 19:13:08	\N
20	Modifier cible	modifier-cible	Permission pour modifier cible	2025-07-24 19:13:08	2025-07-24 19:13:08	\N
21	Supprimer cible	supprimer-cible	Permission pour supprimer cible	2025-07-24 19:13:08	2025-07-24 19:13:08	\N
22	Voir departements	voir-departements	Permission pour voir departements	2025-07-24 19:13:08	2025-07-24 19:13:08	\N
23	Gerer departements	gerer-departements	Permission pour gerer departements	2025-07-24 19:13:08	2025-07-24 19:13:08	\N
24	Voir communes	voir-communes	Permission pour voir communes	2025-07-24 19:13:08	2025-07-24 19:13:08	\N
25	Gerer communes	gerer-communes	Permission pour gerer communes	2025-07-24 19:13:08	2025-07-24 19:13:08	\N
26	Voir arrondissements	voir-arrondissements	Permission pour voir arrondissements	2025-07-24 19:13:08	2025-07-24 19:13:08	\N
27	Gerer arrondissements	gerer-arrondissements	Permission pour gerer arrondissements	2025-07-24 19:13:08	2025-07-24 19:13:08	\N
28	Voir villages	voir-villages	Permission pour voir villages	2025-07-24 19:13:08	2025-07-24 19:13:08	\N
29	Gerer villages	gerer-villages	Permission pour gerer villages	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
30	Recevoir une notification validation idee projet	recevoir-une-notification-validation-idee-projet	Permission pour recevoir une notification validation idee projet	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
31	Recevoir une notification resultat idee	recevoir-une-notification-resultat-idee	Permission pour recevoir une notification resultat idee	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
32	Voir grands secteurs	voir-grands-secteurs	Permission pour voir grands secteurs	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
33	Voir secteurs	voir-secteurs	Permission pour voir secteurs	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
34	Gerer secteurs	gerer-secteurs	Permission pour gerer secteurs	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
35	Voir sous secteurs	voir-sous-secteurs	Permission pour voir sous secteurs	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
36	Voir types intervention	voir-types-intervention	Permission pour voir types intervention	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
37	Gerer types intervention	gerer-types-intervention	Permission pour gerer types intervention	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
38	Voir types financement	voir-types-financement	Permission pour voir types financement	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
39	Voir natures financement	voir-natures-financement	Permission pour voir natures financement	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
40	Voir sources financement	voir-sources-financement	Permission pour voir sources financement	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
41	Gerer financement	gerer-financement	Permission pour gerer financement	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
42	Voir axes pag	voir-axes-pag	Permission pour voir axes pag	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
43	Voir piliers pag	voir-piliers-pag	Permission pour voir piliers pag	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
44	Voir actions pag	voir-actions-pag	Permission pour voir actions pag	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
45	Voir orientations pnd	voir-orientations-pnd	Permission pour voir orientations pnd	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
46	Voir objectifs pnd	voir-objectifs-pnd	Permission pour voir objectifs pnd	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
47	Voir resultats pnd	voir-resultats-pnd	Permission pour voir resultats pnd	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
48	Voir categories projet	voir-categories-projet	Permission pour voir categories projet	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
49	Gerer categories projet	gerer-categories-projet	Permission pour gerer categories projet	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
50	Voir types programme	voir-types-programme	Permission pour voir types programme	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
51	Gerer types programme	gerer-types-programme	Permission pour gerer types programme	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
52	Voir composants programme	voir-composants-programme	Permission pour voir composants programme	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
53	Gerer composants programme	gerer-composants-programme	Permission pour gerer composants programme	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
54	Voir idees projet	voir-idees-projet	Permission pour voir idees projet	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
55	Gerer idees projet	gerer-idees-projet	Permission pour gerer idees projet	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
56	Creer idee projet	creer-idee-projet	Permission pour creer idee projet	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
57	Modifier idee projet	modifier-idee-projet	Permission pour modifier idee projet	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
58	Supprimer idee projet	supprimer-idee-projet	Permission pour supprimer idee projet	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
59	Valider idee projet	valider-idee-projet	Permission pour valider idee projet	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
60	Voir documents	voir-documents	Permission pour voir documents	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
61	Telecharger canevas analyse idee	telecharger-canevas-analyse-idee	Permission pour telecharger canevas analyse idee	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
62	Modifier canevas analyse idee	modifier-canevas-analyse-idee	Permission pour modifier canevas analyse idee	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
63	Voir canevas fiche idee	voir-canevas-fiche-idee	Permission pour voir canevas fiche idee	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
64	Modifier canevas fiche idee	modifier-canevas-fiche-idee	Permission pour modifier canevas fiche idee	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
65	Telecharger canevas fiche idee	telecharger-canevas-fiche-idee	Permission pour telecharger canevas fiche idee	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
66	Modifier canevas grille evaluation climatique	modifier-canevas-grille-evaluation-climatique	Permission pour modifier canevas grille evaluation climatique	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
67	Modifier canevas grille evaluation amc	modifier-canevas-grille-evaluation-amc	Permission pour modifier canevas grille evaluation amc	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
68	Modifier canevas note idee	modifier-canevas-note-idee	Permission pour modifier canevas note idee	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
69	Gerer documents	gerer-documents	Permission pour gerer documents	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
70	Telecharger documents	telecharger-documents	Permission pour telecharger documents	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
71	Creer tdr	creer-tdr	Permission pour creer tdr	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
72	Modifier tdr	modifier-tdr	Permission pour modifier tdr	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
73	Obtenir score climatique	obtenir-score-climatique	Permission pour obtenir score climatique	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
74	Voir tdr prefaisabilite	voir-tdr-prefaisabilite	Permission pour voir tdr prefaisabilite	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
75	Voir tdr faisabilite	voir-tdr-faisabilite	Permission pour voir tdr faisabilite	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
76	Telecharger tdr prefaisabilite	telecharger-tdr-prefaisabilite	Permission pour telecharger tdr prefaisabilite	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
77	Telecharger tdr faisabilite	telecharger-tdr-faisabilite	Permission pour telecharger tdr faisabilite	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
78	Soumettre tdr faisabilite	soumettre-tdr-faisabilite	Permission pour soumettre tdr faisabilite	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
79	Soumettre tdr prefaisabilite	soumettre-tdr-prefaisabilite	Permission pour soumettre tdr prefaisabilite	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
80	Rediger note conception	rediger-note-conception	Permission pour rediger note conception	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
81	Voir note conception	voir-note-conception	Permission pour voir note conception	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
82	Modifier note conception	modifier-note-conception	Permission pour modifier note conception	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
83	Evaluer note conception	evaluer-note-conception	Permission pour evaluer note conception	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
84	Valider note conception	valider-note-conception	Permission pour valider note conception	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
85	Approuver note conception	approuver-note-conception	Permission pour approuver note conception	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
86	Recevoir une notification nouvelle idee projet	recevoir-une-notification-nouvelle-idee-projet	Permission pour recevoir une notification nouvelle idee projet	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
87	Voir evaluations	voir-evaluations	Permission pour voir evaluations	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
88	Creer evaluation	creer-evaluation	Permission pour creer evaluation	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
89	Modifier evaluation	modifier-evaluation	Permission pour modifier evaluation	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
90	Soumettre evaluation	soumettre-evaluation	Permission pour soumettre evaluation	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
91	Apprecier tdr faisabilite	apprecier-tdr-faisabilite	Permission pour apprecier tdr faisabilite	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
92	Valider tdr faisabilite	valider-tdr-faisabilite	Permission pour valider tdr faisabilite	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
93	Apprecier tdr prefaisabilite	apprecier-tdr-prefaisabilite	Permission pour apprecier tdr prefaisabilite	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
94	Valider tdr prefaisabilite	valider-tdr-prefaisabilite	Permission pour valider tdr prefaisabilite	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
95	Valider etude faisabilite	valider-etude-faisabilite	Permission pour valider etude faisabilite	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
96	Valider etude prefaisabilite	valider-etude-prefaisabilite	Permission pour valider etude prefaisabilite	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
97	Soumettre rapport faisabilite	soumettre-rapport-faisabilite	Permission pour soumettre rapport faisabilite	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
98	Soumettre rapport prefaisabilite	soumettre-rapport-prefaisabilite	Permission pour soumettre rapport prefaisabilite	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
99	Voir rapports etude	voir-rapports-etude	Permission pour voir rapports etude	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
100	Valider rapport faisabilite	valider-rapport-faisabilite	Permission pour valider rapport faisabilite	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
101	Valider rapport prefaisabilite	valider-rapport-prefaisabilite	Permission pour valider rapport prefaisabilite	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
102	Voir workflows	voir-workflows	Permission pour voir workflows	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
103	Gerer workflows	gerer-workflows	Permission pour gerer workflows	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
104	Suivre progression	suivre-progression	Permission pour suivre progression	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
105	Generer rapports	generer-rapports	Permission pour generer rapports	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
106	Ajouter commentaire	ajouter-commentaire	Permission pour ajouter commentaire	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
107	Voir commentaires	voir-commentaires	Permission pour voir commentaires	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
108	Modifier commentaire	modifier-commentaire	Permission pour modifier commentaire	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
109	Supprimer commentaire	supprimer-commentaire	Permission pour supprimer commentaire	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
110	Telecharger fichier	telecharger-fichier	Permission pour telecharger fichier	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
111	Upload fichier	upload-fichier	Permission pour upload fichier	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
112	Supprimer fichier	supprimer-fichier	Permission pour supprimer fichier	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
113	Fiche synthese idee projet	fiche-synthese-idee-projet	Permission pour fiche synthese idee projet	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
114	Generer fiche synthese	generer-fiche-synthese	Permission pour generer fiche synthese	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
115	Exporter donnees	exporter-donnees	Permission pour exporter donnees	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
116	Configuration systeme	configuration-systeme	Permission pour configuration systeme	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
117	Gestion logs	gestion-logs	Permission pour gestion logs	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
118	Maintenance systeme	maintenance-systeme	Permission pour maintenance systeme	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
119	Backup donnees	backup-donnees	Permission pour backup donnees	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
\.


--
-- Data for Name: personal_access_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.personal_access_tokens (id, tokenable_type, tokenable_id, name, token, abilities, last_used_at, expires_at, created_at, updated_at) FROM stdin;
23	App\\Models\\User	1	9846fbd43653c159	504c6e427e222041d036680b2a3dc3252646ed5910f89fe5511cc0d524a12582	["*"]	2025-07-25 10:46:47	\N	2025-07-25 10:46:46	2025-07-25 10:46:47
24	App\\Models\\User	1	9d2a7002034f8be0	2661ce51a69aa5b4e1d8b2ec22543eef705f09af067a2e07d06cfcca9ba0ef6a	["*"]	2025-07-25 10:47:33	\N	2025-07-25 10:47:33	2025-07-25 10:47:33
25	App\\Models\\User	1	883d6128d410c544	ed8d5552f71bcb84d933cad2725b6f4ed0245dd1316bc10a2e536b8a46022c35	["*"]	2025-07-25 10:47:36	\N	2025-07-25 10:47:36	2025-07-25 10:47:36
26	App\\Models\\User	1	6d02f948e341fe8d	7110ca1ea2d02fc7e93652d448c31d6b6246c627af08acb958d20fc70b8a16cd	["*"]	2025-07-25 10:54:50	\N	2025-07-25 10:54:50	2025-07-25 10:54:50
27	App\\Models\\User	1	d24c99a6a6418164	24d6cd65ef3702c922d5fbb0e7a6a6394a83bd25ea3b29a9d2315c9c8fde90c5	["*"]	2025-07-25 11:06:43	\N	2025-07-25 11:06:43	2025-07-25 11:06:43
28	App\\Models\\User	1	387416daf2718ad8	6483cff37ab8a0b8245c26d0a48d0b8fb1b92c7f0b2f7d3baa7b1ab589d34b5c	["*"]	2025-07-25 11:32:13	\N	2025-07-25 11:32:12	2025-07-25 11:32:13
\.


--
-- Data for Name: personnes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.personnes (id, nom, prenom, poste, "organismeId", created_at, updated_at, deleted_at) FROM stdin;
1	Administrateur	Système	Super Administrateur	1	2025-07-17 19:42:27	2025-07-17 19:42:27	\N
2	Mukendi	Jean-Pierre	Ministre du Plan	2	2025-07-17 19:42:27	2025-07-17 19:42:27	\N
4	Leclerc	Hugues	Consultant	2	2025-07-17 19:42:27	2025-07-17 19:42:27	\N
5	Le Roux	Céline	Consultant	3	2025-07-17 19:42:27	2025-07-17 19:42:27	\N
6	Herve	Thomas	Coordonnateur	2	2025-07-17 19:42:27	2025-07-17 19:42:27	\N
7	Alves	Susan	Assistant	3	2025-07-17 19:42:27	2025-07-17 19:42:27	\N
8	Pages	Louis	Consultant	2	2025-07-17 19:42:27	2025-07-17 19:42:27	\N
9	Jacquet	Capucine	Chef de Service	5	2025-07-17 19:42:27	2025-07-17 19:42:27	\N
10	Legrand	Noël	Chargé de Programme	3	2025-07-17 19:42:27	2025-07-17 19:42:27	\N
11	Torres	Margaux	Assistant	5	2025-07-17 19:42:27	2025-07-17 19:42:27	\N
12	Peltier	Célina	Consultant	2	2025-07-17 19:42:27	2025-07-17 19:42:27	\N
13	Leger	Olivie	Coordonnateur	2	2025-07-17 19:42:27	2025-07-17 19:42:27	\N
14	Andre	François	Consultant	4	2025-07-17 19:42:27	2025-07-17 19:42:27	\N
15	Maillard	Guillaume	Responsable	2	2025-07-17 19:42:27	2025-07-17 19:42:27	\N
16	Auger	Victor	Expert	3	2025-07-17 19:42:27	2025-07-17 19:42:27	\N
17	Fleury	Rémy	Expert	1	2025-07-17 19:42:27	2025-07-17 19:42:27	\N
18	Clement	Antoinette	Analyste	1	2025-07-17 19:42:27	2025-07-17 19:42:27	\N
19	Vallet	Étienne	Assistant	4	2025-07-17 19:42:27	2025-07-17 19:42:27	\N
20	Boutin	Raymond	Responsable	2	2025-07-17 19:42:27	2025-07-17 19:42:27	\N
21	Diallo	Françoise	Gestionnaire	2	2025-07-17 19:42:27	2025-07-17 19:42:27	\N
22	Bodin	Laurence	Chef de Service	1	2025-07-17 19:42:27	2025-07-17 19:42:27	\N
23	Chartier	Philippe	Coordonnateur	4	2025-07-17 19:42:27	2025-07-17 19:42:27	\N
3	KOTIN	Patrick	Directeur de la Planification	2	2025-07-17 19:42:27	2025-07-18 07:59:49	\N
27	BOCOGA	Corine	\N	2	2025-07-22 06:36:30	2025-07-22 06:36:30	\N
28	BOCOGA	Corine	\N	2	2025-07-22 07:08:11	2025-07-22 07:08:11	\N
29	BOCOGA	Corine	\N	2	2025-07-22 07:12:34	2025-07-22 07:12:34	\N
30	BOCOGA	Corine	\N	2	2025-07-22 08:07:49	2025-07-22 08:07:49	\N
31	BOCOGA	Corine	\N	2	2025-07-22 08:22:27	2025-07-22 08:22:27	\N
32	BOCOGA	Corine	\N	2	2025-07-22 08:35:14	2025-07-22 08:35:14	\N
33	BOCOGA	Corine	\N	2	2025-07-22 08:38:16	2025-07-22 08:38:16	\N
34	BOCOGA	Corine	\N	2	2025-07-22 08:56:18	2025-07-22 08:56:18	\N
35	BOCOGA	Corine	\N	2	2025-07-22 08:59:27	2025-07-22 08:59:27	\N
36	BOCOGA	Corine	\N	2	2025-07-22 09:21:13	2025-07-22 09:21:13	\N
37	BOCOGA	Corine	\N	2	2025-07-22 09:34:29	2025-07-22 09:34:29	\N
74	constantin	Alao	\N	9	2025-07-23 21:29:53	2025-07-23 21:29:53	\N
75	alao	mouta	\N	4	2025-07-24 16:24:54	2025-07-24 16:24:54	\N
76	Super	Admin	\N	7	2025-07-24 19:22:54	2025-07-24 19:22:54	\N
77	Josephine	TOUDONOU	\N	3	2025-07-24 19:27:25	2025-07-24 19:27:25	\N
83	constantin	Alao	\N	9	2025-07-29 02:14:35	2025-07-29 02:14:35	\N
94	DIALLO	Enock	\N	14	2025-07-29 04:18:10	2025-07-29 04:18:10	\N
78	KOTIN	Patrick	\N	2	2025-07-28 13:33:59	2025-07-29 06:14:57	\N
97	DPAF Admin nom	DPAF Admin prenom	\N	\N	2025-07-29 09:52:58	2025-07-29 09:55:33	\N
99	DPAF Admin nom	DPAF Admin prenom	\N	\N	2025-07-29 10:20:45	2025-07-29 10:21:33	\N
100	DIALLO	Enock	admin	\N	2025-07-29 11:28:38	2025-07-29 11:28:38	\N
\.


--
-- Data for Name: projets; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.projets (id, "ideeProjetId", "secteurId", "ministereId", "categorieId", "responsableId", "demandeurId", identifiant_bip, identifiant_sigfp, statut, phase, sous_phase, decision, titre_projet, sigle, type_projet, origine, fondement, situation_actuelle, situation_desiree, contraintes, description_projet, echeancier, description_extrants, caracteristiques, impact_environnement, aspect_organisationnel, risques_immediats, conclusions, description, description_decision, estimation_couts, public_cible, constats_majeurs, objectif_general, sommaire, score_climatique, score_amc, cout_dollar_americain, cout_euro, cout_dollar_canadien, date_debut_etude, date_fin_etude, date_prevue_demarrage, date_effective_demarrage, duree, cout_estimatif_projet, "ficheIdee", parties_prenantes, objectifs_specifiques, resultats_attendus, body_projet, isdeleted, created_at, updated_at, deleted_at) FROM stdin;
\.


--
-- Data for Name: role_permissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.role_permissions (id, "roleId", "permissionId", created_at, updated_at, deleted_at) FROM stdin;
1	1	1	\N	\N	\N
2	1	2	\N	\N	\N
3	1	3	\N	\N	\N
4	1	4	\N	\N	\N
5	1	5	\N	\N	\N
6	1	6	\N	\N	\N
7	1	7	\N	\N	\N
8	1	8	\N	\N	\N
9	1	9	\N	\N	\N
10	1	10	\N	\N	\N
11	1	11	\N	\N	\N
12	1	12	\N	\N	\N
13	1	13	\N	\N	\N
14	1	14	\N	\N	\N
15	1	15	\N	\N	\N
16	1	16	\N	\N	\N
17	1	17	\N	\N	\N
18	1	18	\N	\N	\N
19	1	19	\N	\N	\N
20	1	20	\N	\N	\N
21	1	21	\N	\N	\N
22	1	22	\N	\N	\N
23	1	23	\N	\N	\N
24	1	24	\N	\N	\N
25	1	25	\N	\N	\N
26	1	26	\N	\N	\N
27	1	27	\N	\N	\N
28	1	28	\N	\N	\N
29	1	29	\N	\N	\N
30	1	30	\N	\N	\N
31	1	31	\N	\N	\N
32	1	32	\N	\N	\N
33	1	33	\N	\N	\N
34	1	34	\N	\N	\N
35	1	35	\N	\N	\N
36	1	36	\N	\N	\N
37	1	37	\N	\N	\N
38	1	38	\N	\N	\N
39	1	39	\N	\N	\N
40	1	40	\N	\N	\N
41	1	41	\N	\N	\N
42	1	42	\N	\N	\N
43	1	43	\N	\N	\N
44	1	44	\N	\N	\N
45	1	45	\N	\N	\N
46	1	46	\N	\N	\N
47	1	47	\N	\N	\N
48	1	48	\N	\N	\N
49	1	49	\N	\N	\N
50	1	50	\N	\N	\N
51	1	51	\N	\N	\N
52	1	52	\N	\N	\N
53	1	53	\N	\N	\N
54	1	54	\N	\N	\N
55	1	55	\N	\N	\N
56	1	56	\N	\N	\N
57	1	57	\N	\N	\N
58	1	58	\N	\N	\N
59	1	59	\N	\N	\N
60	1	60	\N	\N	\N
61	1	61	\N	\N	\N
62	1	62	\N	\N	\N
63	1	63	\N	\N	\N
64	1	64	\N	\N	\N
65	1	65	\N	\N	\N
66	1	66	\N	\N	\N
67	1	67	\N	\N	\N
68	1	68	\N	\N	\N
69	1	69	\N	\N	\N
70	1	70	\N	\N	\N
71	1	71	\N	\N	\N
72	1	72	\N	\N	\N
73	1	73	\N	\N	\N
74	1	74	\N	\N	\N
75	1	75	\N	\N	\N
76	1	76	\N	\N	\N
77	1	77	\N	\N	\N
78	1	78	\N	\N	\N
79	1	79	\N	\N	\N
80	1	80	\N	\N	\N
81	1	81	\N	\N	\N
82	1	82	\N	\N	\N
83	1	83	\N	\N	\N
84	1	84	\N	\N	\N
85	1	85	\N	\N	\N
86	1	86	\N	\N	\N
87	1	87	\N	\N	\N
88	1	88	\N	\N	\N
89	1	89	\N	\N	\N
90	1	90	\N	\N	\N
91	1	91	\N	\N	\N
92	1	92	\N	\N	\N
93	1	93	\N	\N	\N
94	1	94	\N	\N	\N
95	1	95	\N	\N	\N
96	1	96	\N	\N	\N
97	1	97	\N	\N	\N
98	1	98	\N	\N	\N
99	1	99	\N	\N	\N
100	1	100	\N	\N	\N
101	1	101	\N	\N	\N
102	1	102	\N	\N	\N
103	1	103	\N	\N	\N
104	1	104	\N	\N	\N
105	1	105	\N	\N	\N
106	1	106	\N	\N	\N
107	1	107	\N	\N	\N
108	1	108	\N	\N	\N
109	1	109	\N	\N	\N
110	1	110	\N	\N	\N
111	1	111	\N	\N	\N
112	1	112	\N	\N	\N
113	1	113	\N	\N	\N
114	1	114	\N	\N	\N
115	1	115	\N	\N	\N
116	1	116	\N	\N	\N
117	1	117	\N	\N	\N
118	1	118	\N	\N	\N
119	1	119	\N	\N	\N
120	2	32	\N	\N	\N
121	2	33	\N	\N	\N
122	2	35	\N	\N	\N
123	2	38	\N	\N	\N
124	2	40	\N	\N	\N
125	2	42	\N	\N	\N
126	2	43	\N	\N	\N
127	2	44	\N	\N	\N
128	2	45	\N	\N	\N
129	2	46	\N	\N	\N
130	2	47	\N	\N	\N
131	2	54	\N	\N	\N
132	2	55	\N	\N	\N
133	2	63	\N	\N	\N
134	2	73	\N	\N	\N
135	2	87	\N	\N	\N
136	2	88	\N	\N	\N
137	3	54	\N	\N	\N
138	3	59	\N	\N	\N
139	3	86	\N	\N	\N
140	5	38	\N	\N	\N
141	5	40	\N	\N	\N
142	5	54	\N	\N	\N
143	5	91	\N	\N	\N
144	5	97	\N	\N	\N
145	5	99	\N	\N	\N
146	6	91	\N	\N	\N
147	6	92	\N	\N	\N
148	7	2	\N	\N	\N
149	7	3	\N	\N	\N
150	7	1	\N	\N	\N
151	7	4	\N	\N	\N
152	7	5	\N	\N	\N
153	7	75	\N	\N	\N
154	7	92	\N	\N	\N
155	7	74	\N	\N	\N
156	7	91	\N	\N	\N
157	7	95	\N	\N	\N
158	7	78	\N	\N	\N
159	7	94	\N	\N	\N
160	7	100	\N	\N	\N
161	7	77	\N	\N	\N
162	7	93	\N	\N	\N
163	7	96	\N	\N	\N
164	7	79	\N	\N	\N
165	7	97	\N	\N	\N
166	7	101	\N	\N	\N
167	7	76	\N	\N	\N
168	7	98	\N	\N	\N
169	7	54	\N	\N	\N
170	7	56	\N	\N	\N
171	7	55	\N	\N	\N
172	7	59	\N	\N	\N
173	7	57	\N	\N	\N
174	7	58	\N	\N	\N
175	7	48	\N	\N	\N
176	7	49	\N	\N	\N
177	7	113	\N	\N	\N
178	7	86	\N	\N	\N
179	7	30	\N	\N	\N
180	8	91	\N	\N	\N
181	8	92	\N	\N	\N
182	8	93	\N	\N	\N
183	8	94	\N	\N	\N
184	8	119	\N	\N	\N
185	8	96	\N	\N	\N
186	8	95	\N	\N	\N
187	8	97	\N	\N	\N
188	8	98	\N	\N	\N
189	8	99	\N	\N	\N
190	8	100	\N	\N	\N
191	8	101	\N	\N	\N
192	8	102	\N	\N	\N
193	8	104	\N	\N	\N
194	8	103	\N	\N	\N
195	8	106	\N	\N	\N
196	8	105	\N	\N	\N
197	8	108	\N	\N	\N
198	8	107	\N	\N	\N
199	8	109	\N	\N	\N
200	8	111	\N	\N	\N
201	8	112	\N	\N	\N
202	8	110	\N	\N	\N
203	8	114	\N	\N	\N
204	8	113	\N	\N	\N
205	8	115	\N	\N	\N
206	8	117	\N	\N	\N
207	8	116	\N	\N	\N
208	8	29	\N	\N	\N
209	8	31	\N	\N	\N
210	8	30	\N	\N	\N
211	8	118	\N	\N	\N
212	8	35	\N	\N	\N
213	8	36	\N	\N	\N
214	8	37	\N	\N	\N
215	8	38	\N	\N	\N
216	8	34	\N	\N	\N
217	8	39	\N	\N	\N
218	8	40	\N	\N	\N
219	8	33	\N	\N	\N
220	8	41	\N	\N	\N
221	8	42	\N	\N	\N
222	8	43	\N	\N	\N
223	8	44	\N	\N	\N
224	8	45	\N	\N	\N
225	8	46	\N	\N	\N
226	8	47	\N	\N	\N
227	8	48	\N	\N	\N
228	8	49	\N	\N	\N
229	8	50	\N	\N	\N
230	8	52	\N	\N	\N
231	8	53	\N	\N	\N
232	8	51	\N	\N	\N
233	8	54	\N	\N	\N
234	8	55	\N	\N	\N
235	8	56	\N	\N	\N
236	8	57	\N	\N	\N
237	8	58	\N	\N	\N
238	8	60	\N	\N	\N
239	8	61	\N	\N	\N
240	8	62	\N	\N	\N
241	8	63	\N	\N	\N
242	8	64	\N	\N	\N
243	8	65	\N	\N	\N
244	8	66	\N	\N	\N
245	8	67	\N	\N	\N
246	9	99	\N	\N	\N
247	9	100	\N	\N	\N
248	10	99	\N	\N	\N
249	10	100	\N	\N	\N
250	11	99	\N	\N	\N
251	11	100	\N	\N	\N
252	12	99	\N	\N	\N
253	12	100	\N	\N	\N
\.


--
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.roles (id, nom, slug, description, roleable_type, roleable_id, created_at, updated_at, deleted_at) FROM stdin;
1	Super Admin	super-admin	Rôle Super Admin pour l'espace administration-general	\N	\N	2025-07-24 19:13:09	2025-07-24 19:13:09	\N
2	Responsable Projet	responsable-projet	Rôle Responsable Projet pour l'espace dpaf	\N	\N	2025-07-24 19:13:10	2025-07-24 19:13:10	\N
3	Responsable Hierachique	responsable-hierachique	Rôle Responsable Hierachique pour l'espace dpaf	\N	\N	2025-07-24 19:13:10	2025-07-24 19:13:10	\N
4	Responsable DGPB	responsable-dgpb	Rôle Responsable DGPB pour l'espace dgpb	\N	\N	2025-07-24 19:13:10	2025-07-24 19:13:10	\N
5	Analyste DGPD	analyste-dgpd	Rôle Analyste DGPD pour l'espace dgpb	\N	\N	2025-07-24 19:13:10	2025-07-24 19:13:10	\N
6	HUH	huh	\N	\N	\N	2025-07-25 12:54:37	2025-07-25 12:54:37	\N
7	DPAF	dpaf	\N	\N	\N	2025-07-28 22:26:54	2025-07-28 22:26:54	\N
8	DGPD	dgpd	\N	\N	\N	2025-07-28 22:30:38	2025-07-28 22:30:38	\N
9	RH	rh	\N	\N	\N	2025-07-29 01:47:37	2025-07-29 01:47:37	\N
10	organisation	organisation	\N	\N	\N	2025-07-29 04:16:13	2025-07-29 04:16:13	\N
11	Responsable Idee	responsable-idee	\N	App\\Models\\Organisation	14	2025-07-29 11:17:09	2025-07-29 11:18:04	\N
12	test assignation	test-assignation	\N	App\\Models\\Organisation	14	2025-07-29 11:18:57	2025-07-29 11:18:57	\N
\.


--
-- Data for Name: secteurs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.secteurs (id, nom, slug, description, type, "secteurId", created_at, updated_at, deleted_at) FROM stdin;
13	Secteur l\\'environnement	secteur-l'environnement	\N	grand-secteur	\N	2025-07-18 10:16:28	2025-07-18 10:19:08	2025-07-18 10:19:08
37	1753789211::98676555	1753789211::98676555	\N	grand-secteur	\N	2025-07-25 12:13:50	2025-07-29 11:40:11	2025-07-29 11:40:11
1	Secteur-environnement	secteur-environnement-2	\N	secteur	4	2025-07-18 09:05:14	2025-07-21 14:10:29	2025-07-21 14:10:29
4	Secteur de l\\'environnement	secteur-de-l'environnement	\N	grand-secteur	\N	2025-07-18 10:05:44	2025-07-21 14:10:55	2025-07-21 14:10:55
16	Secteur-environnement	secteur-environnement-1	\N	sous-secteur	1	2025-07-18 10:16:57	2025-07-21 14:11:32	2025-07-21 14:11:32
11	Secteur de environnement	secteur-de-environnement	\N	grand-secteur	\N	2025-07-18 10:14:16	2025-07-21 14:11:59	2025-07-21 14:11:59
12	Secteur-environnement	secteur-environnement	\N	grand-secteur	\N	2025-07-18 10:16:09	2025-07-21 14:12:02	2025-07-21 14:12:02
14	Insalubrite	insalubrite	\N	secteur	1	2025-07-18 10:16:32	2025-07-21 14:12:07	2025-07-21 14:12:07
15	Insalubrite	insalubrite-1	\N	secteur	1	2025-07-18 10:16:34	2025-07-21 14:12:10	2025-07-21 14:12:10
17	Secteur environnement	secteur-environnement-3	\N	grand-secteur	\N	2025-07-19 08:04:10	2025-07-21 14:12:30	2025-07-21 14:12:30
18	Secteur environnementaux	secteur-environnementaux	\N	grand-secteur	\N	2025-07-21 13:54:18	2025-07-21 14:12:35	2025-07-21 14:12:35
20	Secteur environnehghjhgjhjhment	secteur-environnehghjhgjhjhment	\N	secteur	19	2025-07-21 14:20:48	2025-07-21 14:20:48	\N
21	Secteur environkknehghjhgjhjhment	secteur-environkknehghjhgjhjhment	\N	secteur	17	2025-07-21 14:21:04	2025-07-21 14:21:04	\N
22	Pollution des sols	pollution-des-sols	\N	sous-secteur	19	2025-07-21 14:35:17	2025-07-21 14:35:17	\N
23	Pollution des solss	pollution-des-solss	\N	sous-secteur	19	2025-07-21 16:24:25	2025-07-21 16:24:25	\N
24	Pollution des solsss	pollution-des-solsss	\N	sous-secteur	19	2025-07-21 16:24:37	2025-07-21 16:24:37	\N
25	Pollution des sjjolsbss	pollution-des-sjjolsbss	\N	sous-secteur	19	2025-07-21 16:27:32	2025-07-21 16:27:32	\N
27	Secteur sous-secteur	secteur-sous-secteur	\N	sous-secteur	20	2025-07-22 20:27:23	2025-07-22 20:27:23	\N
28	Secteur so-secteur	secteur-so-secteur	\N	sous-secteur	20	2025-07-22 21:40:00	2025-07-22 21:40:00	\N
29	Secteur sofgdf-secteur	secteur-sofgdf-secteur	\N	sous-secteur	20	2025-07-23 04:06:49	2025-07-23 04:06:49	\N
30	Secteur sofgd4ref-secteur	secteur-sofgd4ref-secteur	\N	sous-secteur	4	2025-07-23 04:07:03	2025-07-23 04:07:03	\N
31	Secteur sofgd4reftfghgfh	secteur-sofgd4reftfghgfh	\N	sous-secteur	20	2025-07-23 04:31:19	2025-07-23 04:31:19	\N
32	Insdfdalubrite	insdfdalubrite	\N	sous-secteur	20	2025-07-23 04:33:22	2025-07-23 04:34:42	\N
34	molo	molo	\N	grand-secteur	\N	2025-07-23 07:36:32	2025-07-23 07:36:32	\N
39	trdt	trdt	\N	secteur	19	2025-07-29 12:21:48	2025-07-29 12:21:48	\N
35	1753362479::Secteur enfant de agriculture	1753362479::secteur-enfant-de-agriculture	\N	grand-secteur	\N	2025-07-23 10:57:25	2025-07-24 13:07:59	2025-07-24 13:07:59
36	gggg	gggg	\N	grand-secteur	\N	2025-07-25 11:28:00	2025-07-25 11:28:00	\N
33	Testyyyu	testyyyu	\N	grand-secteur	\N	2025-07-23 06:19:10	2025-07-25 12:21:44	\N
38	grand-secteur 3	grand-secteur-3	\N	grand-secteur	\N	2025-07-29 10:58:57	2025-07-29 10:58:57	\N
19	Secteur Agriculture kjjk	secteur-agriculture-kjjk	\N	grand-secteur	\N	2025-07-21 14:14:58	2025-07-29 11:40:05	\N
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
CytFelTZgMJk3HB7WrgBAPhWy2LmKPkkE3mEnZxd	\N	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiWW5vUEN0aXlacnlSVktqMk5LR1lUaVBGZ09EclRjRUNWcTExTWJpMiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE1OiJodHRwOi8vZWViMmFjNDRjZmRhLm5ncm9rLWZyZWUuYXBwL2F1dGgvY2FsbGJhY2s/Y29kZT1kZmVkYzhmOC1iYTU1LTRlYWQtYWMwNS0wMjg2ZDk0OTAwNDQuOTZlODFiMDEtZWFiZi00ZDUzLWFjZTYtZjFhZGYzYzkzN2EyLjFjZTljOTAzLWM4Y2ItNDg2My04NTRmLTRlYjljYjE5NjQ4ZiZzZXNzaW9uX3N0YXRlPTk2ZTgxYjAxLWVhYmYtNGQ1My1hY2U2LWYxYWRmM2M5MzdhMiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1753308876
buD2vSC4SK8yCcUoQzSEhcUUwZvQAzTKtuP1wlfQ	\N	192.168.8.130	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiaFVtbzdoS1ZhQXdYbHVaNzdqNzlyM3BZeHpmcTMyeFJLNTZjVkxWUSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjA2OiJodHRwOi8vMTkyLjE2OC44LjEzMDo4MDAwL2F1dGgvY2FsbGJhY2s/Y29kZT03MTZkN2UzOC0wNzE1LTRkMmUtOWU4ZS1lZGE0MTcwODA5NTYuOTI0MDJhNjQtMTVkOC00MWY0LWI0MTItNTQzZTQ1MGNmNDcwLjFjZTljOTAzLWM4Y2ItNDg2My04NTRmLTRlYjljYjE5NjQ4ZiZzZXNzaW9uX3N0YXRlPTkyNDAyYTY0LTE1ZDgtNDFmNC1iNDEyLTU0M2U0NTBjZjQ3MCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1753310760
tAZ8oi5xO10odREvAEDL4jzw9ZafUOigmhrRkxw6	\N	192.168.8.103	WhatsApp/2.23.20.0	YTozOntzOjY6Il90b2tlbiI7czo0MDoieWZpSFhLQUNrcnVLdGJEU0tVeU0wT0N2VWRFYjRtNUVNMHk1WnQyciI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly8yZTk5OWYyMTgzNWEubmdyb2stZnJlZS5hcHAiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1753360004
g57aybvscsemlPiy00Pjsm427ZuluScbdnAzqbvg	\N	192.168.8.103	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)	YTozOntzOjY6Il90b2tlbiI7czo0MDoiWTFpc0NTY2V4S2ZSWGpRNjF0dlNnTGZMNGo4eUlnNnRNT3AxWGJycyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly83Zjg1OGJkNWIwNmEubmdyb2stZnJlZS5hcHAiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1753378005
KC5WFcJ7kOH4z5f9AFaBbGonY8ehwIYLPfk2doIT	\N	192.168.8.103	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0	YTozOntzOjY6Il90b2tlbiI7czo0MDoiNGgwWnBUVG5QMUpsajBBeEh2M3V5THlaZFhudUlKTEQ4eVFMZ1pKaiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly83Zjg1OGJkNWIwNmEubmdyb2stZnJlZS5hcHAiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1753378032
8NrqoUSU2kBVuUGipqIYryNGlHK4PzzQdqyZxaF6	\N	192.168.8.108	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiM3ZhdXlPN3RuZURXeW5UQTQ4RFJRYXI2M2twSUJmUGtDbTNFOFVZYyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHA6Ly8xOTIuMTY4LjguMTAzOjgwMDAiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1753391648
FlzxS5o2wRQFWuLNEtuOMrq3z4bmn57kYQj7w0CM	\N	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiR3lqU0xLak1ySGp1SmxEVWNtRUsySjJRenNlMlNtRG85aFF0TFJMQyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly84MTc3YmVkMzJhMGYubmdyb2stZnJlZS5hcHAiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1753482832
oAgbmg6ykev5kLfKeA9fajWQLjj6W49ayBHJxOWP	\N	192.168.8.128	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoidXgxaktSOHJEWUJjT3FFOVNEb2xEekhGWDVsa0JxRGdFQVNBN1VvMiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHA6Ly8xOTIuMTY4LjguMTI4OjgwMDAiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1753691914
fkLSUFfi5K7qfXgAcbX9bVUt23WCbiJPkMz1f6Zn	\N	192.168.8.130	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiYzJ2UHhkUHJQUVNuTU82cHB6VHlrbkt6dWNYdG84ZldZd3pHQXhiaiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjA2OiJodHRwOi8vMTkyLjE2OC44LjEzMDo4MDAwL2F1dGgvY2FsbGJhY2s/Y29kZT1lYzFlN2E4NC05ZWQ1LTQwNmItOTE5MS03Zjc0ZTIyNmUzYzEuNDFiYzlmNWItOGFlMC00NTU3LWIyYmYtZmFlMjA2ODYzMzBkLjFjZTljOTAzLWM4Y2ItNDg2My04NTRmLTRlYjljYjE5NjQ4ZiZzZXNzaW9uX3N0YXRlPTQxYmM5ZjViLThhZTAtNDU1Ny1iMmJmLWZhZTIwNjg2MzMwZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1753310307
mRiwVTYuUm63FNPO4nB0v0XV2gUpGQlaG9HUSunu	\N	192.168.8.103	WhatsApp/2.23.20.0	YTozOntzOjY6Il90b2tlbiI7czo0MDoiOThDN1BWTWw1c1o4TEtING1LZmJLOGFoUHFqV2o2MzFnTDF3bnFjVyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly83Zjg1OGJkNWIwNmEubmdyb2stZnJlZS5hcHAiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1753377982
tKqabpOWLe1IIeLBqW4NESAS8BkvpEgtsCOSOXvo	\N	192.168.8.103	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)	YTozOntzOjY6Il90b2tlbiI7czo0MDoiRFF4d29kMkFQbkNNSnEyNFg5WUtDdHp4T0c0ODNXYjVpT2FQa0diRCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly83Zjg1OGJkNWIwNmEubmdyb2stZnJlZS5hcHAiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1753378005
ncNzqWNjqJ5L6o3suo42YkTJvPXxgbuXzD7e9xvF	\N	192.168.8.103	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoid1NoNHZxWEdhMVVsQzRJSTlsZVNveTVvc1UzaW5vZ3N4SjlXeWFkYSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly83Zjg1OGJkNWIwNmEubmdyb2stZnJlZS5hcHAiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1753381211
bbI6EjyV19ocbjqQlYtaPkBH5PsD10yePdR1M5Xo	\N	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiODY4TEp0SUhnVXhHNThZc0N5V1pJQ2M3SnJXT2VjSnI5TnJnZVM3RiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly84MTc3YmVkMzJhMGYubmdyb2stZnJlZS5hcHAiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1753483448
a4iCse5Yoww5SlnBUFVDQMHD828RkaSaA3qCIHg0	\N	192.168.8.121	PostmanRuntime/7.44.1	YTozOntzOjY6Il90b2tlbiI7czo0MDoiUHlPeDRqcDR5Wm5tRzMwZ25VUEdqVURsaGV1M1J5QmlWczduRUlESiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHA6Ly8xOTIuMTY4LjguMTI4OjgwMDAiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1753702438
zUX0OZseGPYK6AzULqNrLJtyarNpSErIwYFmuZyT	\N	192.168.8.104	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiSm1Wenk5azZ1dFloVHVMM0RRUlRnRkJkVDFqTUVZdnd1SjJnbGF2bSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzk6Imh0dHA6Ly8xOTIuMTY4LjguMTMwOjgwMDAvYXV0aC9jYWxsYmFjayI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1753304850
j6LWiW8Wi1JJ4jixPKHzr3VabNkY2dUChfyCN6g5	\N	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiRWF4d2pyTDdpcDNnN2diSWhDU3dqMGlMaHVIQzFtOWNma092R1EwSyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8wLjAuMC4wOjgwMDAvYXV0aC9jYWxsYmFjayI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1753305089
9KW6QNj211xmsMjUbU03RLuKMV5yWC6etk7WXKOR	\N	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiM2FxbW5HSzhDNzlFb0NrUzR0cThuZFI4MnM1a1BsMHhsTGZBcW80MSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjAyOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYXV0aC9jYWxsYmFjaz9jb2RlPWE4MDc2ZWJlLWQ4ZmYtNDllOC1iMjdmLTU5MjVhMzcyYTIwMS5iNzIwNmI2ZS1lMTEwLTQxZTUtYThhYy1jZDM5YjQ5OGE3MzkuMWNlOWM5MDMtYzhjYi00ODYzLTg1NGYtNGViOWNiMTk2NDhmJnNlc3Npb25fc3RhdGU9YjcyMDZiNmUtZTExMC00MWU1LWE4YWMtY2QzOWI0OThhNzM5Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1753306289
srCW2WbUpLhLCW7OKdA1LKx9nFEu9CRqYb5Bkm2z	\N	192.168.8.130	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiRkljYUwxRFVFMHdJR3dCNU11UzFyRExxUm5zaURqbjBRMFYxZkpBTiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjA2OiJodHRwOi8vMTkyLjE2OC44LjEzMDo4MDAwL2F1dGgvY2FsbGJhY2s/Y29kZT03ZTZjZmEwZi1hYjgzLTQyZjItYjNkMy01ZDhkNzMxYTE2ZjYuODViZWM3MjYtNjQyMi00OTM5LTk2OTktZDRhYjcxZWRiZmUzLjFjZTljOTAzLWM4Y2ItNDg2My04NTRmLTRlYjljYjE5NjQ4ZiZzZXNzaW9uX3N0YXRlPTg1YmVjNzI2LTY0MjItNDkzOS05Njk5LWQ0YWI3MWVkYmZlMyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1753306480
rIem92d7Odor1RpDZdncI3gdokTXLHN6oRvQXa4q	\N	192.168.8.130	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiSmV3WlJLamFoMVBxNUczMEo0NzVUdVIwMW1yczAxZWNJNHFjR2Q4NSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjA2OiJodHRwOi8vMTkyLjE2OC44LjEzMDo4MDAwL2F1dGgvY2FsbGJhY2s/Y29kZT0wMWIwOWM1Yy03MGViLTQwMDktOGYwMC04NGE0NmUxZDdjMDAuODE2MTVjY2UtYTU0My00ZTM1LTliMjgtZWI0NDMzOTY2MjgyLjFjZTljOTAzLWM4Y2ItNDg2My04NTRmLTRlYjljYjE5NjQ4ZiZzZXNzaW9uX3N0YXRlPTgxNjE1Y2NlLWE1NDMtNGUzNS05YjI4LWViNDQzMzk2NjI4MiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1753310620
ogxljqgbDAj5r7ynJk4sZDmqDkjWNPMEGh9GGWRt	\N	192.168.8.103	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiYXpvOFpQWEhFbGJTZXNpeFRWN3FlWmpXN2RZYmZQdHhTTFpScGpUbSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly83Zjg1OGJkNWIwNmEubmdyb2stZnJlZS5hcHAiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1753378004
zds2aqdp3Dm4b3oD7rerji08kpS6lvuVadYGq0Ji	\N	192.168.8.103	Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)	YTozOntzOjY6Il90b2tlbiI7czo0MDoiTWZpTGxheDNBMGNLRmpkQ0J5b2kxSUtJTU1zRGhkb1dGNEk1bk9qTCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly83Zjg1OGJkNWIwNmEubmdyb2stZnJlZS5hcHAiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1753378006
zGQq8isgSszpBX4s34XLiGccA7Yeqtz79yG16Ao1	\N	192.168.8.103	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiRjhlc2NPNFRoS1p6RVR3VGVSaHVNZHpkUFdjQUtqMHB1RWlQdWpQeiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly83Zjg1OGJkNWIwNmEubmdyb2stZnJlZS5hcHAiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1753381476
1hqyj7IXeW8k9HkMshKRxREusjMfd2RlowSYyUQR	\N	192.168.8.130	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoibElwMXU2bzJweWdoaTd1ajZGZUg4V2kzMWowUzd6UGZvcHBPSlNQTSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjA2OiJodHRwOi8vMTkyLjE2OC44LjEzMDo4MDAwL2F1dGgvY2FsbGJhY2s/Y29kZT0wYzI1MGFkMy0xZDJkLTQ5N2YtOTExMC1iZjExYWVmYTkzOTUuMGEzYTY1NjEtOTYyMi00ZjhiLTlmNDYtMzJjM2IxMWZiY2I3LjFjZTljOTAzLWM4Y2ItNDg2My04NTRmLTRlYjljYjE5NjQ4ZiZzZXNzaW9uX3N0YXRlPTBhM2E2NTYxLTk2MjItNGY4Yi05ZjQ2LTMyYzNiMTFmYmNiNyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1753306760
t5BWAQRffXal5EVjxgbmegGfVsg4wL98GYpY0bwv	\N	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiWGhYN3A2RWNOaVZkQ1Rram1CaFdtcmZsSGxPOXFTYnpVVFFPbllWdSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly84MTc3YmVkMzJhMGYubmdyb2stZnJlZS5hcHAiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1753483475
6xJ26Grd1JPcYoN9U5JzqqogeZ5FTj1xWOw60cCN	\N	192.168.8.130	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiYjNMZHpNaHVOcFlLSEtlZVBabG9VZUtYSGZPVmRaYlRiRzdaM0lwSSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjA2OiJodHRwOi8vMTkyLjE2OC44LjEzMDo4MDAwL2F1dGgvY2FsbGJhY2s/Y29kZT0zNzZhMjNkMS04ZDdhLTRmODMtYjQxYi1lMjc0ZjFlYTA2ZWQuY2ViNTM5NzUtOGRhOC00ODlmLTlmZDMtZjA3MGJmMTU4MWViLjFjZTljOTAzLWM4Y2ItNDg2My04NTRmLTRlYjljYjE5NjQ4ZiZzZXNzaW9uX3N0YXRlPWNlYjUzOTc1LThkYTgtNDg5Zi05ZmQzLWYwNzBiZjE1ODFlYiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1753306857
zK1NMizodx1JwnIg6Onwt369tOOXbI9ExBhLItCq	\N	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiSjFJUW1iR3dtdmJQbklPdlhEQmNuWk5mZ05QazJJaGdoNWdTMUo1TCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjAyOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXV0aC9jYWxsYmFjaz9jb2RlPTMwMzJhNzlhLTExNmEtNGFjNi04OWJjLTEyODQzZDA4NjM2Ny41MGI4NWMxYS02YTZmLTQ1OTUtOTUwMy05YmNjOGU2NjgzNTcuMWNlOWM5MDMtYzhjYi00ODYzLTg1NGYtNGViOWNiMTk2NDhmJnNlc3Npb25fc3RhdGU9NTBiODVjMWEtNmE2Zi00NTk1LTk1MDMtOWJjYzhlNjY4MzU3Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1753307514
8F1lIaWwyHfG0SCBgl94JiMtPl9bcUOyRXDfMBhV	\N	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiZHozb3o0Wm8wQVdUdlp6VGJVVUtaTFJnOW5hRTFUVTNpYUw5Q29EdiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly9lZWIyYWM0NGNmZGEubmdyb2stZnJlZS5hcHAiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1753308415
E1qRb96cIBkfP3aQmSrKGpGUkaR6Yz4PuJcNVwjy	\N	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiVWZScmdYVTRJS1VEVnY0NjNyMzNVdU90ZXhONmlVbW9DTXh0TmNsbiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE1OiJodHRwOi8vZWViMmFjNDRjZmRhLm5ncm9rLWZyZWUuYXBwL2F1dGgvY2FsbGJhY2s/Y29kZT0yMzRkM2VkNi05NDBkLTQ3YjYtYjU5ZC01ZDg4MzQzZDViNTQuNTBiODVjMWEtNmE2Zi00NTk1LTk1MDMtOWJjYzhlNjY4MzU3LjFjZTljOTAzLWM4Y2ItNDg2My04NTRmLTRlYjljYjE5NjQ4ZiZzZXNzaW9uX3N0YXRlPTUwYjg1YzFhLTZhNmYtNDU5NS05NTAzLTliY2M4ZTY2ODM1NyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1753308701
sCHgRfqEe3wMBespoEOoMaDg3lo6ljHtSkKT4Bp3	\N	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiMUNKZzAxVmR2MUJwdjZ5UnVJeG5LT25XV2JJcUp5M21VVldPUkpaRSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE1OiJodHRwOi8vZWViMmFjNDRjZmRhLm5ncm9rLWZyZWUuYXBwL2F1dGgvY2FsbGJhY2s/Y29kZT0yMzRkM2VkNi05NDBkLTQ3YjYtYjU5ZC01ZDg4MzQzZDViNTQuNTBiODVjMWEtNmE2Zi00NTk1LTk1MDMtOWJjYzhlNjY4MzU3LjFjZTljOTAzLWM4Y2ItNDg2My04NTRmLTRlYjljYjE5NjQ4ZiZzZXNzaW9uX3N0YXRlPTUwYjg1YzFhLTZhNmYtNDU5NS05NTAzLTliY2M4ZTY2ODM1NyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1753308702
\.


--
-- Data for Name: sources_financement_projets; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sources_financement_projets (id, "sourceId", projetable_type, projetable_id, created_at, updated_at, deleted_at) FROM stdin;
6	10	App\\Models\\IdeeProjet	55	2025-07-24 06:19:59	2025-07-24 06:19:59	\N
7	10	App\\Models\\IdeeProjet	57	2025-07-24 06:40:45	2025-07-24 06:40:45	\N
9	10	App\\Models\\IdeeProjet	91	2025-07-24 08:34:15	2025-07-24 08:34:15	\N
10	10	App\\Models\\IdeeProjet	92	2025-07-24 08:36:09	2025-07-24 08:36:09	\N
11	10	App\\Models\\IdeeProjet	93	2025-07-24 08:39:19	2025-07-24 08:39:19	\N
12	10	App\\Models\\IdeeProjet	94	2025-07-24 08:42:19	2025-07-24 08:42:19	\N
13	10	App\\Models\\IdeeProjet	95	2025-07-24 08:53:46	2025-07-24 08:53:46	\N
14	10	App\\Models\\IdeeProjet	96	2025-07-24 08:55:15	2025-07-24 08:55:15	\N
15	10	App\\Models\\IdeeProjet	97	2025-07-24 08:55:29	2025-07-24 08:55:29	\N
78	10	App\\Models\\IdeeProjet	172	2025-07-24 10:42:51	2025-07-24 10:42:51	\N
79	10	App\\Models\\IdeeProjet	173	2025-07-24 10:43:47	2025-07-24 10:43:47	\N
80	10	App\\Models\\IdeeProjet	174	2025-07-24 10:55:38	2025-07-24 10:55:38	\N
81	10	App\\Models\\IdeeProjet	175	2025-07-24 10:56:24	2025-07-24 10:56:24	\N
82	10	App\\Models\\IdeeProjet	176	2025-07-24 11:00:39	2025-07-24 11:00:39	\N
83	10	App\\Models\\IdeeProjet	7	2025-07-24 20:02:00	2025-07-24 20:02:00	\N
84	10	App\\Models\\IdeeProjet	8	2025-07-24 20:02:39	2025-07-24 20:02:39	\N
87	10	App\\Models\\IdeeProjet	13	2025-07-24 20:17:45	2025-07-24 20:17:45	\N
88	10	App\\Models\\IdeeProjet	15	2025-07-24 20:47:49	2025-07-24 20:47:49	\N
89	10	App\\Models\\IdeeProjet	16	2025-07-24 21:16:35	2025-07-24 21:16:35	\N
90	10	App\\Models\\IdeeProjet	17	2025-07-25 10:15:00	2025-07-25 10:15:00	\N
91	10	App\\Models\\IdeeProjet	19	2025-07-25 10:50:17	2025-07-25 10:50:17	\N
92	10	App\\Models\\IdeeProjet	20	2025-07-25 10:54:17	2025-07-25 10:54:17	\N
93	10	App\\Models\\IdeeProjet	21	2025-07-25 10:54:34	2025-07-25 10:54:34	\N
97	10	App\\Models\\IdeeProjet	26	2025-07-25 11:17:37	2025-07-25 11:17:37	\N
98	10	App\\Models\\IdeeProjet	27	2025-07-25 11:59:40	2025-07-25 11:59:40	\N
99	14	App\\Models\\IdeeProjet	31	2025-07-25 16:07:47	2025-07-25 16:07:47	\N
100	14	App\\Models\\IdeeProjet	32	2025-07-25 16:10:21	2025-07-25 16:10:21	\N
101	14	App\\Models\\IdeeProjet	33	2025-07-25 16:12:16	2025-07-25 16:12:16	\N
102	10	App\\Models\\IdeeProjet	34	2025-07-25 16:29:19	2025-07-25 16:29:19	\N
103	14	App\\Models\\IdeeProjet	35	2025-07-25 16:43:25	2025-07-25 16:43:25	\N
104	10	App\\Models\\IdeeProjet	36	2025-07-25 16:51:49	2025-07-25 16:51:49	\N
105	10	App\\Models\\IdeeProjet	37	2025-07-25 17:27:39	2025-07-25 17:27:39	\N
106	14	App\\Models\\IdeeProjet	38	2025-07-25 17:30:52	2025-07-25 17:30:52	\N
107	10	App\\Models\\IdeeProjet	39	2025-07-25 18:15:38	2025-07-25 18:15:38	\N
108	10	App\\Models\\IdeeProjet	40	2025-07-25 20:18:16	2025-07-25 20:18:16	\N
109	10	App\\Models\\IdeeProjet	42	2025-07-25 20:28:15	2025-07-25 20:28:15	\N
110	10	App\\Models\\IdeeProjet	43	2025-07-25 20:28:25	2025-07-25 20:28:25	\N
111	10	App\\Models\\IdeeProjet	44	2025-07-25 20:28:33	2025-07-25 20:28:33	\N
112	10	App\\Models\\IdeeProjet	45	2025-07-25 21:16:23	2025-07-25 21:16:23	\N
113	10	App\\Models\\IdeeProjet	62	2025-07-28 11:27:54	2025-07-28 11:27:54	\N
53	10	App\\Models\\IdeeProjet	135	2025-07-24 09:28:55	2025-07-24 09:28:55	\N
54	10	App\\Models\\IdeeProjet	136	2025-07-24 09:30:40	2025-07-24 09:30:40	\N
116	14	App\\Models\\IdeeProjet	73	2025-07-29 08:44:19	2025-07-29 08:44:19	\N
57	10	App\\Models\\IdeeProjet	139	2025-07-24 09:36:25	2025-07-24 09:36:25	\N
58	10	App\\Models\\IdeeProjet	140	2025-07-24 09:36:40	2025-07-24 09:36:40	\N
59	10	App\\Models\\IdeeProjet	141	2025-07-24 09:37:49	2025-07-24 09:37:49	\N
60	10	App\\Models\\IdeeProjet	144	2025-07-24 09:42:42	2025-07-24 09:42:42	\N
61	10	App\\Models\\IdeeProjet	145	2025-07-24 09:43:05	2025-07-24 09:43:05	\N
62	10	App\\Models\\IdeeProjet	146	2025-07-24 09:44:19	2025-07-24 09:44:19	\N
63	10	App\\Models\\IdeeProjet	147	2025-07-24 09:46:02	2025-07-24 09:46:02	\N
64	10	App\\Models\\IdeeProjet	148	2025-07-24 09:46:55	2025-07-24 09:46:55	\N
\.


--
-- Data for Name: statuts; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.statuts (id, statut, date, statutable_type, statutable_id, avis, created_at, updated_at, deleted_at) FROM stdin;
\.


--
-- Data for Name: track_infos; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.track_infos (id, update_data, track_info_type, track_info_id, description, "createdAt", "createdBy", "updatedAt", "updateBy", created_at, updated_at, deleted_at) FROM stdin;
\.


--
-- Data for Name: types_intervention; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.types_intervention (id, type_intervention, "secteurId", created_at, updated_at, deleted_at) FROM stdin;
1	Public cible	1	2025-07-18 10:38:59	2025-07-18 10:45:03	2025-07-18 10:45:03
2	Type d'intervention	14	2025-07-18 10:58:08	2025-07-18 11:05:04	\N
\.


--
-- Data for Name: types_intervention_projets; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.types_intervention_projets (id, "typeId", projetable_type, projetable_id, created_at, updated_at, deleted_at) FROM stdin;
\.


--
-- Data for Name: types_programme; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.types_programme (id, type_programme, slug, "typeId", created_at, updated_at, deleted_at) FROM stdin;
1	PAG	pag	\N	2025-07-18 11:52:09	2025-07-18 11:52:09	\N
2	PND	pnd	\N	2025-07-18 11:55:28	2025-07-18 11:55:28	\N
3	PC2D	pc2d	\N	2025-07-18 11:55:42	2025-07-18 11:55:42	\N
5	Pillier	pillier	1	2025-07-18 12:08:55	2025-07-18 12:17:39	\N
10	1752841395::Effets attendus	1752841395::effets-attendus	\N	2025-07-18 12:22:49	2025-07-18 12:23:15	2025-07-18 12:23:15
11	effets attendus	effets-attendus	3	2025-07-18 12:25:32	2025-07-18 12:25:32	\N
12	Resultats attendus	resultats-attendus	3	2025-07-18 12:25:42	2025-07-18 12:25:42	\N
4	Axe du pag	axe-pag	1	2025-07-18 12:08:42	2025-07-23 14:16:09	\N
9	Resultats strategique du pnd	resultats-strategique-pnd	2	2025-07-18 12:18:56	2025-07-23 14:23:56	\N
8	Objectif strategique du pnd	objectif-strategique-pnd	7	2025-07-18 12:18:45	2025-07-23 14:26:19	\N
7	Orientation strategique du pnd	orientation-strategique-pnd	2	2025-07-18 12:18:16	2025-07-23 14:27:50	\N
6	Pilier du pag	pilier-pag	4	2025-07-18 12:09:20	2025-07-23 14:29:45	\N
13	Action du pag	action-pag	4	2025-07-23 20:02:19	2025-07-23 20:02:19	\N
14	esrhgj	esrhgj	8	2025-07-23 23:57:40	2025-07-23 23:57:40	\N
15	1753315440::Tyrthjglkjl	1753315440::tyrthjglkjl	12	2025-07-24 00:02:47	2025-07-24 00:04:00	2025-07-24 00:04:00
\.


--
-- Data for Name: user_roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.user_roles (id, "roleId", "userId", created_at, updated_at, deleted_at) FROM stdin;
1	1	2	2025-07-29 01:58:57	2025-07-29 01:58:57	\N
2	1	1	2025-07-29 02:04:01	2025-07-29 02:04:01	\N
3	9	12	2025-07-29 02:14:35	2025-07-29 02:14:35	\N
4	10	15	2025-07-29 04:18:10	2025-07-29 04:18:10	\N
5	7	18	2025-07-29 09:52:58	2025-07-29 09:52:58	\N
6	8	20	2025-07-29 10:20:45	2025-07-29 10:20:45	\N
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, provider, provider_user_id, username, email, status, is_email_verified, email_verified_at, password, "personneId", "roleId", last_connection, ip_address, remember_token, created_at, updated_at, deleted_at, settings, person, keycloak_id, type, "lastRequest", profilable_id, profilable_type, account_verification_request_sent_at, password_update_at, last_password_remember, token, link_is_valide) FROM stdin;
2	keycloack	cbocoga@gmail.com	cbocoga@gmail.com	cbocoga@gmail.com	actif	f	\N	$2y$12$84A8wbkvWV2KRobIv8gG5.QqrrBJz8QJqRh0VR12QLLKl31RJLxne	77	4	\N	\N	\N	2025-07-24 19:27:25	2025-07-24 19:27:25	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	f
15	keycloack	corinebocog@gmail.com	corinebocog@gmail.com	corinebocog@gmail.com	actif	f	\N	$2y$12$UFrQ764unV9JtzqSx9QgHuE8Ma3MXuJ51Qz8UV4YpwfqwTcZQW9a6	94	10	\N	\N	\N	2025-07-29 04:18:10	2025-07-29 11:10:05	\N	\N	\N	\N	organisation	2025-07-29 11:04:46	14	App\\Models\\Organisation	\N	\N	\N	\N	f
21	local	corinebocoga99@gmail.com	corinebocoga99@gmail.com	corinebocoga99@gmail.com	actif	f	\N	$2y$12$mcuDF2zJy1SjiPv8NOn43.YYjQ.DIS0LKHVnKp0BYskiHr7A.srai	100	\N	\N	\N	\N	2025-07-29 11:28:39	2025-07-29 11:28:40	\N	\N	\N	\N	\N	\N	\N	\N	2025-07-29 11:28:39	\N	\N	$2y$12$SgAnzNvMuomtk70iJZFiOm2e5tPkSRY6J2UNKXH3lvIpuiUyV6	t
3	keycloack	alaomoutawakil@gmail.com	alaomoutawakil@gmail.com	kotin.patrick@plan.gov.cd	actif	f	\N	$2y$12$Fx7I7hy8vMxKVZTwMVuk3.iGmWLAu7kOXGCOkq3VfXfsmafQqRgXe	78	3	2025-07-28 18:05:44	\N	\N	2025-07-28 13:33:59	2025-07-29 06:14:57	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	f
18	keycloack	corinedbocog@gmail.com	corinedbocog@gmail.com	corinedbocog@gmail.com	actif	f	\N	$2y$12$zztBTzkosD14HT.re6548uGvWW39Gk1i9W0jutQxoS8iL.58c6l2K	97	7	\N	\N	\N	2025-07-29 09:52:58	2025-07-29 09:53:00	\N	\N	\N	\N	dpaf	\N	3	App\\Models\\Dpaf	2025-07-29 09:52:58	\N	\N	$2y$12$feII5HRVdlsmSkBwcPuRuiCiGlB9oVaURGlQvTQdwSEb2oSqcn	t
1	keycloack	mbouraima@celeriteholding.com	mbouraima@celeriteholding.com	mbouraima@celeriteholding.com	actif	f	\N	$2y$12$84A8wbkvWV2KRobIv8gG5.QqrrBJz8QJqRh0VR12QLLKl31RJLxne	76	1	2025-07-29 01:39:11	\N	\N	2025-07-24 19:22:54	2025-07-29 01:39:11	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	f
20	keycloack	cocodfdfrine999@gmail.com	cocodfdfrine999@gmail.com	cocodfdfrine999@gmail.com	actif	f	\N	$2y$12$Cu3lRa9ks.pI26pkVUMmpe/H0xU6ibv6taqZ0MhhlsNbB6c.B7.pK	99	8	\N	\N	\N	2025-07-29 10:20:45	2025-07-29 10:20:47	\N	\N	\N	\N	dgpd	\N	2	App\\Models\\Dgpd	2025-07-29 10:20:45	\N	\N	$2y$12$4Hap7YQ1e9XEaXUgrGYMu8TT0H5oADHv8u4eqAm048anex1c8UhO	t
12	keycloack	cocorine999@gmail.com	cocorine999@gmail.com	cocorine999@gmail.com	actif	f	2025-07-29 02:21:54	$2y$12$UFrQ764unV9JtzqSx9QgHuE8Ma3MXuJ51Qz8UV4YpwfqwTcZQW9a6	83	9	\N	\N	\N	2025-07-29 02:14:35	2025-07-29 10:31:07	\N	\N	\N	\N	rh	2025-07-29 10:31:07	\N	\N	\N	\N	\N	\N	f
\.


--
-- Data for Name: villages; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.villages (id, code, nom, slug, "arrondissementId", created_at, updated_at, deleted_at) FROM stdin;
2	AL-BAN	BOFOUNOU	bofounou	1	2025-07-29 08:44:06	2025-07-29 08:44:06	\N
\.


--
-- Data for Name: workflows; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.workflows (id, statut, phase, sous_phase, date, projetable_type, projetable_id, created_at, updated_at, deleted_at) FROM stdin;
\.


--
-- Name: arrondissements_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.arrondissements_id_seq', 546, true);


--
-- Name: categories_critere_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.categories_critere_id_seq', 7, true);


--
-- Name: categories_document_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.categories_document_id_seq', 6, true);


--
-- Name: categories_projet_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.categories_projet_id_seq', 1, true);


--
-- Name: champs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.champs_id_seq', 214, true);


--
-- Name: champs_projet_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.champs_projet_id_seq', 1832, true);


--
-- Name: champs_sections_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.champs_sections_id_seq', 44, true);


--
-- Name: cibles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.cibles_id_seq', 5, true);


--
-- Name: cibles_projets_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.cibles_projets_id_seq', 160, true);


--
-- Name: commentaires_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.commentaires_id_seq', 1, false);


--
-- Name: communes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.communes_id_seq', 77, true);


--
-- Name: composants_programme_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.composants_programme_id_seq', 16, true);


--
-- Name: composants_projet_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.composants_projet_id_seq', 399, true);


--
-- Name: criteres_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.criteres_id_seq', 7, true);


--
-- Name: decisions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.decisions_id_seq', 1, false);


--
-- Name: departements_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.departements_id_seq', 12, true);


--
-- Name: dgpd_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.dgpd_id_seq', 2, true);


--
-- Name: documents_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.documents_id_seq', 40, true);


--
-- Name: dpaf_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.dpaf_id_seq', 3, true);


--
-- Name: evaluation_champs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.evaluation_champs_id_seq', 1, false);


--
-- Name: evaluation_criteres_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.evaluation_criteres_id_seq', 1, false);


--
-- Name: evaluations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.evaluations_id_seq', 1, false);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: financements_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.financements_id_seq', 14, true);


--
-- Name: groupe_utilisateur_roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.groupe_utilisateur_roles_id_seq', 9, true);


--
-- Name: groupe_utilisateur_users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.groupe_utilisateur_users_id_seq', 2, true);


--
-- Name: groupes_utilisateur_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.groupes_utilisateur_id_seq', 6, true);


--
-- Name: idees_projet_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.idees_projet_id_seq', 74, true);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.jobs_id_seq', 12, true);


--
-- Name: lieux_intervention_projets_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.lieux_intervention_projets_id_seq', 1, true);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 210, true);


--
-- Name: notations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.notations_id_seq', 20, true);


--
-- Name: odds_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.odds_id_seq', 12, true);


--
-- Name: odds_projets_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.odds_projets_id_seq', 141, true);


--
-- Name: organisations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.organisations_id_seq', 15, true);


--
-- Name: permissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.permissions_id_seq', 119, true);


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.personal_access_tokens_id_seq', 28, true);


--
-- Name: personnes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.personnes_id_seq', 100, true);


--
-- Name: projets_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.projets_id_seq', 1, false);


--
-- Name: role_permissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.role_permissions_id_seq', 253, true);


--
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.roles_id_seq', 12, true);


--
-- Name: secteurs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.secteurs_id_seq', 39, true);


--
-- Name: sources_financement_projets_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.sources_financement_projets_id_seq', 117, true);


--
-- Name: statuts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.statuts_id_seq', 1, false);


--
-- Name: track_infos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.track_infos_id_seq', 1, false);


--
-- Name: types_intervention_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.types_intervention_id_seq', 2, true);


--
-- Name: types_intervention_projets_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.types_intervention_projets_id_seq', 1, false);


--
-- Name: types_programme_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.types_programme_id_seq', 15, true);


--
-- Name: user_roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.user_roles_id_seq', 6, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 21, true);


--
-- Name: villages_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.villages_id_seq', 2, true);


--
-- Name: workflows_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.workflows_id_seq', 1, false);


--
-- Name: arrondissements arrondissements_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.arrondissements
    ADD CONSTRAINT arrondissements_code_unique UNIQUE (code);


--
-- Name: arrondissements arrondissements_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.arrondissements
    ADD CONSTRAINT arrondissements_pkey PRIMARY KEY (id);


--
-- Name: arrondissements arrondissements_slug_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.arrondissements
    ADD CONSTRAINT arrondissements_slug_unique UNIQUE (slug);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: categories_critere categories_critere_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categories_critere
    ADD CONSTRAINT categories_critere_pkey PRIMARY KEY (id);


--
-- Name: categories_critere categories_critere_slug_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categories_critere
    ADD CONSTRAINT categories_critere_slug_unique UNIQUE (slug);


--
-- Name: categories_critere categories_critere_type_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categories_critere
    ADD CONSTRAINT categories_critere_type_unique UNIQUE (type);


--
-- Name: categories_document categories_document_nom_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categories_document
    ADD CONSTRAINT categories_document_nom_unique UNIQUE (nom);


--
-- Name: categories_document categories_document_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categories_document
    ADD CONSTRAINT categories_document_pkey PRIMARY KEY (id);


--
-- Name: categories_document categories_document_slug_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categories_document
    ADD CONSTRAINT categories_document_slug_unique UNIQUE (slug);


--
-- Name: categories_projet categories_projet_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categories_projet
    ADD CONSTRAINT categories_projet_pkey PRIMARY KEY (id);


--
-- Name: categories_projet categories_projet_slug_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categories_projet
    ADD CONSTRAINT categories_projet_slug_unique UNIQUE (slug);


--
-- Name: champs champs_attribut_sectionid_documentid_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.champs
    ADD CONSTRAINT champs_attribut_sectionid_documentid_unique UNIQUE (attribut, "sectionId", "documentId");


--
-- Name: champs champs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.champs
    ADD CONSTRAINT champs_pkey PRIMARY KEY (id);


--
-- Name: champs_projet champs_projet_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.champs_projet
    ADD CONSTRAINT champs_projet_pkey PRIMARY KEY (id);


--
-- Name: champs_sections champs_sections_intitule_documentid_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.champs_sections
    ADD CONSTRAINT champs_sections_intitule_documentid_unique UNIQUE (intitule, "documentId");


--
-- Name: champs_sections champs_sections_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.champs_sections
    ADD CONSTRAINT champs_sections_pkey PRIMARY KEY (id);


--
-- Name: champs_sections champs_sections_slug_documentid_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.champs_sections
    ADD CONSTRAINT champs_sections_slug_documentid_unique UNIQUE (slug, "documentId");


--
-- Name: cibles cibles_cible_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cibles
    ADD CONSTRAINT cibles_cible_unique UNIQUE (cible);


--
-- Name: cibles cibles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cibles
    ADD CONSTRAINT cibles_pkey PRIMARY KEY (id);


--
-- Name: cibles_projets cibles_projets_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cibles_projets
    ADD CONSTRAINT cibles_projets_pkey PRIMARY KEY (id);


--
-- Name: cibles cibles_slug_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cibles
    ADD CONSTRAINT cibles_slug_unique UNIQUE (slug);


--
-- Name: commentaires commentaires_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.commentaires
    ADD CONSTRAINT commentaires_pkey PRIMARY KEY (id);


--
-- Name: communes communes_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.communes
    ADD CONSTRAINT communes_code_unique UNIQUE (code);


--
-- Name: communes communes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.communes
    ADD CONSTRAINT communes_pkey PRIMARY KEY (id);


--
-- Name: communes communes_slug_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.communes
    ADD CONSTRAINT communes_slug_unique UNIQUE (slug);


--
-- Name: composants_programme composants_programme_intitule_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.composants_programme
    ADD CONSTRAINT composants_programme_intitule_unique UNIQUE (intitule);


--
-- Name: composants_programme composants_programme_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.composants_programme
    ADD CONSTRAINT composants_programme_pkey PRIMARY KEY (id);


--
-- Name: composants_programme composants_programme_slug_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.composants_programme
    ADD CONSTRAINT composants_programme_slug_unique UNIQUE (slug);


--
-- Name: composants_projet composants_projet_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.composants_projet
    ADD CONSTRAINT composants_projet_pkey PRIMARY KEY (id);


--
-- Name: criteres criteres_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.criteres
    ADD CONSTRAINT criteres_pkey PRIMARY KEY (id);


--
-- Name: decisions decisions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.decisions
    ADD CONSTRAINT decisions_pkey PRIMARY KEY (id);


--
-- Name: departements departements_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.departements
    ADD CONSTRAINT departements_code_unique UNIQUE (code);


--
-- Name: departements departements_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.departements
    ADD CONSTRAINT departements_pkey PRIMARY KEY (id);


--
-- Name: departements departements_slug_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.departements
    ADD CONSTRAINT departements_slug_unique UNIQUE (slug);


--
-- Name: dgpd dgpd_nom_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dgpd
    ADD CONSTRAINT dgpd_nom_unique UNIQUE (nom);


--
-- Name: dgpd dgpd_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dgpd
    ADD CONSTRAINT dgpd_pkey PRIMARY KEY (id);


--
-- Name: dgpd dgpd_slug_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dgpd
    ADD CONSTRAINT dgpd_slug_unique UNIQUE (slug);


--
-- Name: documents documents_nom_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.documents
    ADD CONSTRAINT documents_nom_unique UNIQUE (nom);


--
-- Name: documents documents_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.documents
    ADD CONSTRAINT documents_pkey PRIMARY KEY (id);


--
-- Name: documents documents_slug_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.documents
    ADD CONSTRAINT documents_slug_unique UNIQUE (slug);


--
-- Name: dpaf dpaf_nom_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dpaf
    ADD CONSTRAINT dpaf_nom_unique UNIQUE (nom);


--
-- Name: dpaf dpaf_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dpaf
    ADD CONSTRAINT dpaf_pkey PRIMARY KEY (id);


--
-- Name: dpaf dpaf_slug_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dpaf
    ADD CONSTRAINT dpaf_slug_unique UNIQUE (slug);


--
-- Name: evaluation_champs evaluation_champs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.evaluation_champs
    ADD CONSTRAINT evaluation_champs_pkey PRIMARY KEY (id);


--
-- Name: evaluation_criteres evaluation_criteres_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.evaluation_criteres
    ADD CONSTRAINT evaluation_criteres_pkey PRIMARY KEY (id);


--
-- Name: evaluations evaluations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.evaluations
    ADD CONSTRAINT evaluations_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: financements financements_nom_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.financements
    ADD CONSTRAINT financements_nom_unique UNIQUE (nom);


--
-- Name: financements financements_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.financements
    ADD CONSTRAINT financements_pkey PRIMARY KEY (id);


--
-- Name: financements financements_slug_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.financements
    ADD CONSTRAINT financements_slug_unique UNIQUE (slug);


--
-- Name: groupe_utilisateur_roles groupe_utilisateur_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.groupe_utilisateur_roles
    ADD CONSTRAINT groupe_utilisateur_roles_pkey PRIMARY KEY (id);


--
-- Name: groupe_utilisateur_users groupe_utilisateur_users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.groupe_utilisateur_users
    ADD CONSTRAINT groupe_utilisateur_users_pkey PRIMARY KEY (id);


--
-- Name: groupes_utilisateur groupes_utilisateur_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.groupes_utilisateur
    ADD CONSTRAINT groupes_utilisateur_pkey PRIMARY KEY (id);


--
-- Name: idees_projet idees_projet_identifiant_bip_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.idees_projet
    ADD CONSTRAINT idees_projet_identifiant_bip_unique UNIQUE (identifiant_bip);


--
-- Name: idees_projet idees_projet_identifiant_sigfp_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.idees_projet
    ADD CONSTRAINT idees_projet_identifiant_sigfp_unique UNIQUE (identifiant_sigfp);


--
-- Name: idees_projet idees_projet_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.idees_projet
    ADD CONSTRAINT idees_projet_pkey PRIMARY KEY (id);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: lieux_intervention_projets lieux_intervention_projets_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lieux_intervention_projets
    ADD CONSTRAINT lieux_intervention_projets_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: notations notations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notations
    ADD CONSTRAINT notations_pkey PRIMARY KEY (id);


--
-- Name: oauth_access_tokens oauth_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.oauth_access_tokens
    ADD CONSTRAINT oauth_access_tokens_pkey PRIMARY KEY (id);


--
-- Name: oauth_auth_codes oauth_auth_codes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.oauth_auth_codes
    ADD CONSTRAINT oauth_auth_codes_pkey PRIMARY KEY (id);


--
-- Name: oauth_clients oauth_clients_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.oauth_clients
    ADD CONSTRAINT oauth_clients_pkey PRIMARY KEY (id);


--
-- Name: oauth_device_codes oauth_device_codes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.oauth_device_codes
    ADD CONSTRAINT oauth_device_codes_pkey PRIMARY KEY (id);


--
-- Name: oauth_device_codes oauth_device_codes_user_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.oauth_device_codes
    ADD CONSTRAINT oauth_device_codes_user_code_unique UNIQUE (user_code);


--
-- Name: oauth_refresh_tokens oauth_refresh_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.oauth_refresh_tokens
    ADD CONSTRAINT oauth_refresh_tokens_pkey PRIMARY KEY (id);


--
-- Name: odds odds_odd_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.odds
    ADD CONSTRAINT odds_odd_unique UNIQUE (odd);


--
-- Name: odds odds_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.odds
    ADD CONSTRAINT odds_pkey PRIMARY KEY (id);


--
-- Name: odds_projets odds_projets_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.odds_projets
    ADD CONSTRAINT odds_projets_pkey PRIMARY KEY (id);


--
-- Name: odds odds_slug_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.odds
    ADD CONSTRAINT odds_slug_unique UNIQUE (slug);


--
-- Name: organisations organisations_nom_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.organisations
    ADD CONSTRAINT organisations_nom_unique UNIQUE (nom);


--
-- Name: organisations organisations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.organisations
    ADD CONSTRAINT organisations_pkey PRIMARY KEY (id);


--
-- Name: organisations organisations_slug_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.organisations
    ADD CONSTRAINT organisations_slug_unique UNIQUE (slug);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: permissions permissions_nom_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_nom_unique UNIQUE (nom);


--
-- Name: permissions permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (id);


--
-- Name: permissions permissions_slug_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_slug_unique UNIQUE (slug);


--
-- Name: personal_access_tokens personal_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_token_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_token_unique UNIQUE (token);


--
-- Name: personnes personnes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personnes
    ADD CONSTRAINT personnes_pkey PRIMARY KEY (id);


--
-- Name: projets projets_identifiant_bip_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.projets
    ADD CONSTRAINT projets_identifiant_bip_unique UNIQUE (identifiant_bip);


--
-- Name: projets projets_identifiant_sigfp_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.projets
    ADD CONSTRAINT projets_identifiant_sigfp_unique UNIQUE (identifiant_sigfp);


--
-- Name: projets projets_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.projets
    ADD CONSTRAINT projets_pkey PRIMARY KEY (id);


--
-- Name: role_permissions role_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_permissions
    ADD CONSTRAINT role_permissions_pkey PRIMARY KEY (id);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: roles roles_slug_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_slug_unique UNIQUE (slug);


--
-- Name: secteurs secteurs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.secteurs
    ADD CONSTRAINT secteurs_pkey PRIMARY KEY (id);


--
-- Name: secteurs secteurs_slug_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.secteurs
    ADD CONSTRAINT secteurs_slug_unique UNIQUE (slug);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: sources_financement_projets sources_financement_projets_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sources_financement_projets
    ADD CONSTRAINT sources_financement_projets_pkey PRIMARY KEY (id);


--
-- Name: statuts statuts_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.statuts
    ADD CONSTRAINT statuts_pkey PRIMARY KEY (id);


--
-- Name: track_infos track_infos_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.track_infos
    ADD CONSTRAINT track_infos_pkey PRIMARY KEY (id);


--
-- Name: types_intervention types_intervention_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.types_intervention
    ADD CONSTRAINT types_intervention_pkey PRIMARY KEY (id);


--
-- Name: types_intervention_projets types_intervention_projets_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.types_intervention_projets
    ADD CONSTRAINT types_intervention_projets_pkey PRIMARY KEY (id);


--
-- Name: types_intervention types_intervention_type_intervention_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.types_intervention
    ADD CONSTRAINT types_intervention_type_intervention_unique UNIQUE (type_intervention);


--
-- Name: types_programme types_programme_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.types_programme
    ADD CONSTRAINT types_programme_pkey PRIMARY KEY (id);


--
-- Name: types_programme types_programme_slug_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.types_programme
    ADD CONSTRAINT types_programme_slug_unique UNIQUE (slug);


--
-- Name: types_programme types_programme_type_programme_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.types_programme
    ADD CONSTRAINT types_programme_type_programme_unique UNIQUE (type_programme);


--
-- Name: notations unique_annotation_per_categorie_critere; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notations
    ADD CONSTRAINT unique_annotation_per_categorie_critere UNIQUE (libelle, valeur, critere_id, categorie_critere_id);


--
-- Name: criteres unique_critere_nom_per_categorie; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.criteres
    ADD CONSTRAINT unique_critere_nom_per_categorie UNIQUE (intitule, categorie_critere_id);


--
-- Name: groupes_utilisateur unique_groupe_nom_per_profilable; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.groupes_utilisateur
    ADD CONSTRAINT unique_groupe_nom_per_profilable UNIQUE (nom, slug, profilable_type, profilable_id);


--
-- Name: roles unique_role_nom_per_roleable; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT unique_role_nom_per_roleable UNIQUE (nom, slug, roleable_type, roleable_id);


--
-- Name: user_roles user_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_roles
    ADD CONSTRAINT user_roles_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_keycloak_id_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_keycloak_id_unique UNIQUE (keycloak_id);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: users users_username_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_username_unique UNIQUE (username);


--
-- Name: villages villages_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.villages
    ADD CONSTRAINT villages_code_unique UNIQUE (code);


--
-- Name: villages villages_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.villages
    ADD CONSTRAINT villages_pkey PRIMARY KEY (id);


--
-- Name: villages villages_slug_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.villages
    ADD CONSTRAINT villages_slug_unique UNIQUE (slug);


--
-- Name: workflows workflows_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.workflows
    ADD CONSTRAINT workflows_pkey PRIMARY KEY (id);


--
-- Name: champs_projet_projetable_type_projetable_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX champs_projet_projetable_type_projetable_id_index ON public.champs_projet USING btree (projetable_type, projetable_id);


--
-- Name: cibles_projets_projetable_type_projetable_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cibles_projets_projetable_type_projetable_id_index ON public.cibles_projets USING btree (projetable_type, projetable_id);


--
-- Name: commentaires_commentaireable_type_commentaireable_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX commentaires_commentaireable_type_commentaireable_id_index ON public.commentaires USING btree (commentaireable_type, commentaireable_id);


--
-- Name: composants_projet_projetable_type_projetable_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX composants_projet_projetable_type_projetable_id_index ON public.composants_projet USING btree (projetable_type, projetable_id);


--
-- Name: groupes_utilisateur_profilable_type_profilable_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX groupes_utilisateur_profilable_type_profilable_id_index ON public.groupes_utilisateur USING btree (profilable_type, profilable_id);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: lieux_intervention_projets_projetable_type_projetable_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX lieux_intervention_projets_projetable_type_projetable_id_index ON public.lieux_intervention_projets USING btree (projetable_type, projetable_id);


--
-- Name: oauth_access_tokens_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX oauth_access_tokens_user_id_index ON public.oauth_access_tokens USING btree (user_id);


--
-- Name: oauth_auth_codes_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX oauth_auth_codes_user_id_index ON public.oauth_auth_codes USING btree (user_id);


--
-- Name: oauth_clients_owner_type_owner_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX oauth_clients_owner_type_owner_id_index ON public.oauth_clients USING btree (owner_type, owner_id);


--
-- Name: oauth_device_codes_client_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX oauth_device_codes_client_id_index ON public.oauth_device_codes USING btree (client_id);


--
-- Name: oauth_device_codes_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX oauth_device_codes_user_id_index ON public.oauth_device_codes USING btree (user_id);


--
-- Name: oauth_refresh_tokens_access_token_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX oauth_refresh_tokens_access_token_id_index ON public.oauth_refresh_tokens USING btree (access_token_id);


--
-- Name: odds_projets_projetable_type_projetable_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX odds_projets_projetable_type_projetable_id_index ON public.odds_projets USING btree (projetable_type, projetable_id);


--
-- Name: personal_access_tokens_expires_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX personal_access_tokens_expires_at_index ON public.personal_access_tokens USING btree (expires_at);


--
-- Name: personal_access_tokens_tokenable_type_tokenable_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON public.personal_access_tokens USING btree (tokenable_type, tokenable_id);


--
-- Name: roles_roleable_type_roleable_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX roles_roleable_type_roleable_id_index ON public.roles USING btree (roleable_type, roleable_id);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: sources_financement_projets_projetable_type_projetable_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sources_financement_projets_projetable_type_projetable_id_index ON public.sources_financement_projets USING btree (projetable_type, projetable_id);


--
-- Name: statuts_statutable_type_statutable_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX statuts_statutable_type_statutable_id_index ON public.statuts USING btree (statutable_type, statutable_id);


--
-- Name: track_infos_track_info_type_track_info_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX track_infos_track_info_type_track_info_id_index ON public.track_infos USING btree (track_info_type, track_info_id);


--
-- Name: types_intervention_projets_projetable_type_projetable_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX types_intervention_projets_projetable_type_projetable_id_index ON public.types_intervention_projets USING btree (projetable_type, projetable_id);


--
-- Name: workflows_projetable_type_projetable_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX workflows_projetable_type_projetable_id_index ON public.workflows USING btree (projetable_type, projetable_id);


--
-- Name: arrondissements arrondissements_communeid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.arrondissements
    ADD CONSTRAINT arrondissements_communeid_foreign FOREIGN KEY ("communeId") REFERENCES public.communes(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: champs champs_documentid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.champs
    ADD CONSTRAINT champs_documentid_foreign FOREIGN KEY ("documentId") REFERENCES public.documents(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: champs_projet champs_projet_champid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.champs_projet
    ADD CONSTRAINT champs_projet_champid_foreign FOREIGN KEY ("champId") REFERENCES public.champs(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: champs champs_sectionid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.champs
    ADD CONSTRAINT champs_sectionid_foreign FOREIGN KEY ("sectionId") REFERENCES public.champs_sections(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: champs_sections champs_sections_documentid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.champs_sections
    ADD CONSTRAINT champs_sections_documentid_foreign FOREIGN KEY ("documentId") REFERENCES public.documents(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: cibles_projets cibles_projets_cibleid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cibles_projets
    ADD CONSTRAINT cibles_projets_cibleid_foreign FOREIGN KEY ("cibleId") REFERENCES public.cibles(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: commentaires commentaires_commentateurid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.commentaires
    ADD CONSTRAINT commentaires_commentateurid_foreign FOREIGN KEY ("commentateurId") REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: communes communes_departementid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.communes
    ADD CONSTRAINT communes_departementid_foreign FOREIGN KEY ("departementId") REFERENCES public.departements(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: composants_programme composants_programme_typeid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.composants_programme
    ADD CONSTRAINT composants_programme_typeid_foreign FOREIGN KEY ("typeId") REFERENCES public.types_programme(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: composants_projet composants_projet_composantid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.composants_projet
    ADD CONSTRAINT composants_projet_composantid_foreign FOREIGN KEY ("composantId") REFERENCES public.composants_programme(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: criteres criteres_categorie_critere_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.criteres
    ADD CONSTRAINT criteres_categorie_critere_id_foreign FOREIGN KEY (categorie_critere_id) REFERENCES public.categories_critere(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: decisions decisions_observateurid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.decisions
    ADD CONSTRAINT decisions_observateurid_foreign FOREIGN KEY ("observateurId") REFERENCES public.personnes(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: documents documents_categorieid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.documents
    ADD CONSTRAINT documents_categorieid_foreign FOREIGN KEY ("categorieId") REFERENCES public.categories_document(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: evaluation_champs evaluation_champs_champid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.evaluation_champs
    ADD CONSTRAINT evaluation_champs_champid_foreign FOREIGN KEY ("champId") REFERENCES public.champs(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: evaluation_champs evaluation_champs_evaluationid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.evaluation_champs
    ADD CONSTRAINT evaluation_champs_evaluationid_foreign FOREIGN KEY ("evaluationId") REFERENCES public.evaluations(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: evaluation_criteres evaluation_criteres_categorie_critere_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.evaluation_criteres
    ADD CONSTRAINT evaluation_criteres_categorie_critere_id_foreign FOREIGN KEY (categorie_critere_id) REFERENCES public.categories_critere(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: evaluation_criteres evaluation_criteres_critere_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.evaluation_criteres
    ADD CONSTRAINT evaluation_criteres_critere_id_foreign FOREIGN KEY (critere_id) REFERENCES public.criteres(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: evaluation_criteres evaluation_criteres_evaluateur_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.evaluation_criteres
    ADD CONSTRAINT evaluation_criteres_evaluateur_id_foreign FOREIGN KEY (evaluateur_id) REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: evaluation_criteres evaluation_criteres_notation_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.evaluation_criteres
    ADD CONSTRAINT evaluation_criteres_notation_id_foreign FOREIGN KEY (notation_id) REFERENCES public.notations(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: financements financements_financementid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.financements
    ADD CONSTRAINT financements_financementid_foreign FOREIGN KEY ("financementId") REFERENCES public.financements(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: groupe_utilisateur_roles groupe_utilisateur_roles_groupeutilisateurid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.groupe_utilisateur_roles
    ADD CONSTRAINT groupe_utilisateur_roles_groupeutilisateurid_foreign FOREIGN KEY ("groupeUtilisateurId") REFERENCES public.groupes_utilisateur(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: groupe_utilisateur_roles groupe_utilisateur_roles_roleid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.groupe_utilisateur_roles
    ADD CONSTRAINT groupe_utilisateur_roles_roleid_foreign FOREIGN KEY ("roleId") REFERENCES public.roles(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: groupe_utilisateur_users groupe_utilisateur_users_groupeutilisateurid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.groupe_utilisateur_users
    ADD CONSTRAINT groupe_utilisateur_users_groupeutilisateurid_foreign FOREIGN KEY ("groupeUtilisateurId") REFERENCES public.groupes_utilisateur(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: groupe_utilisateur_users groupe_utilisateur_users_userid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.groupe_utilisateur_users
    ADD CONSTRAINT groupe_utilisateur_users_userid_foreign FOREIGN KEY ("userId") REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: idees_projet idees_projet_categorieid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.idees_projet
    ADD CONSTRAINT idees_projet_categorieid_foreign FOREIGN KEY ("categorieId") REFERENCES public.categories_projet(id) ON DELETE CASCADE;


--
-- Name: idees_projet idees_projet_demandeurid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.idees_projet
    ADD CONSTRAINT idees_projet_demandeurid_foreign FOREIGN KEY ("demandeurId") REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: idees_projet idees_projet_ministereid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.idees_projet
    ADD CONSTRAINT idees_projet_ministereid_foreign FOREIGN KEY ("ministereId") REFERENCES public.organisations(id) ON DELETE SET NULL;


--
-- Name: idees_projet idees_projet_responsableid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.idees_projet
    ADD CONSTRAINT idees_projet_responsableid_foreign FOREIGN KEY ("responsableId") REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: idees_projet idees_projet_secteurid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.idees_projet
    ADD CONSTRAINT idees_projet_secteurid_foreign FOREIGN KEY ("secteurId") REFERENCES public.secteurs(id) ON DELETE CASCADE;


--
-- Name: lieux_intervention_projets lieux_intervention_projets_arrondissementid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lieux_intervention_projets
    ADD CONSTRAINT lieux_intervention_projets_arrondissementid_foreign FOREIGN KEY ("arrondissementId") REFERENCES public.arrondissements(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: lieux_intervention_projets lieux_intervention_projets_communeid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lieux_intervention_projets
    ADD CONSTRAINT lieux_intervention_projets_communeid_foreign FOREIGN KEY ("communeId") REFERENCES public.communes(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: lieux_intervention_projets lieux_intervention_projets_departementid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lieux_intervention_projets
    ADD CONSTRAINT lieux_intervention_projets_departementid_foreign FOREIGN KEY ("departementId") REFERENCES public.departements(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: lieux_intervention_projets lieux_intervention_projets_villageid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lieux_intervention_projets
    ADD CONSTRAINT lieux_intervention_projets_villageid_foreign FOREIGN KEY ("villageId") REFERENCES public.villages(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: notations notations_categorie_critere_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notations
    ADD CONSTRAINT notations_categorie_critere_id_foreign FOREIGN KEY (categorie_critere_id) REFERENCES public.categories_critere(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: notations notations_critere_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notations
    ADD CONSTRAINT notations_critere_id_foreign FOREIGN KEY (critere_id) REFERENCES public.criteres(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: odds_projets odds_projets_oddid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.odds_projets
    ADD CONSTRAINT odds_projets_oddid_foreign FOREIGN KEY ("oddId") REFERENCES public.odds(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: organisations organisations_parentid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.organisations
    ADD CONSTRAINT organisations_parentid_foreign FOREIGN KEY ("parentId") REFERENCES public.organisations(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: personnes personnes_organismeid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personnes
    ADD CONSTRAINT personnes_organismeid_foreign FOREIGN KEY ("organismeId") REFERENCES public.organisations(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: projets projets_categorieid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.projets
    ADD CONSTRAINT projets_categorieid_foreign FOREIGN KEY ("categorieId") REFERENCES public.categories_projet(id) ON DELETE CASCADE;


--
-- Name: projets projets_demandeurid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.projets
    ADD CONSTRAINT projets_demandeurid_foreign FOREIGN KEY ("demandeurId") REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: projets projets_ideeprojetid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.projets
    ADD CONSTRAINT projets_ideeprojetid_foreign FOREIGN KEY ("ideeProjetId") REFERENCES public.idees_projet(id) ON DELETE SET NULL;


--
-- Name: projets projets_ministereid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.projets
    ADD CONSTRAINT projets_ministereid_foreign FOREIGN KEY ("ministereId") REFERENCES public.organisations(id) ON DELETE SET NULL;


--
-- Name: projets projets_responsableid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.projets
    ADD CONSTRAINT projets_responsableid_foreign FOREIGN KEY ("responsableId") REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: projets projets_secteurid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.projets
    ADD CONSTRAINT projets_secteurid_foreign FOREIGN KEY ("secteurId") REFERENCES public.secteurs(id) ON DELETE CASCADE;


--
-- Name: role_permissions role_permissions_permissionid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_permissions
    ADD CONSTRAINT role_permissions_permissionid_foreign FOREIGN KEY ("permissionId") REFERENCES public.permissions(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: role_permissions role_permissions_roleid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_permissions
    ADD CONSTRAINT role_permissions_roleid_foreign FOREIGN KEY ("roleId") REFERENCES public.roles(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: secteurs secteurs_secteurid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.secteurs
    ADD CONSTRAINT secteurs_secteurid_foreign FOREIGN KEY ("secteurId") REFERENCES public.secteurs(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: sources_financement_projets sources_financement_projets_sourceid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sources_financement_projets
    ADD CONSTRAINT sources_financement_projets_sourceid_foreign FOREIGN KEY ("sourceId") REFERENCES public.financements(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: track_infos track_infos_createdby_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.track_infos
    ADD CONSTRAINT track_infos_createdby_foreign FOREIGN KEY ("createdBy") REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: track_infos track_infos_updateby_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.track_infos
    ADD CONSTRAINT track_infos_updateby_foreign FOREIGN KEY ("updateBy") REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: types_intervention_projets types_intervention_projets_typeid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.types_intervention_projets
    ADD CONSTRAINT types_intervention_projets_typeid_foreign FOREIGN KEY ("typeId") REFERENCES public.types_intervention(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: types_intervention types_intervention_secteurid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.types_intervention
    ADD CONSTRAINT types_intervention_secteurid_foreign FOREIGN KEY ("secteurId") REFERENCES public.secteurs(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: types_programme types_programme_typeid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.types_programme
    ADD CONSTRAINT types_programme_typeid_foreign FOREIGN KEY ("typeId") REFERENCES public.types_programme(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: user_roles user_roles_roleid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_roles
    ADD CONSTRAINT user_roles_roleid_foreign FOREIGN KEY ("roleId") REFERENCES public.roles(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: user_roles user_roles_userid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_roles
    ADD CONSTRAINT user_roles_userid_foreign FOREIGN KEY ("userId") REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: users users_personneid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_personneid_foreign FOREIGN KEY ("personneId") REFERENCES public.personnes(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: users users_roleid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_roleid_foreign FOREIGN KEY ("roleId") REFERENCES public.roles(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: villages villages_arrondissementid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.villages
    ADD CONSTRAINT villages_arrondissementid_foreign FOREIGN KEY ("arrondissementId") REFERENCES public.arrondissements(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE USAGE ON SCHEMA public FROM PUBLIC;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

