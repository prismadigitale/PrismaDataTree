# PrismaDataTree

PrismaDataTree è un'applicazione web basata su **Laravel 12** e **Filament 5** progettata per la gestione avanzata, la visualizzazione e la ricerca di dati strutturati ad albero (Nodi).
L'applicazione permette non solo di gestire gerarchie complesse di informazioni, ma di tipizzarle e dotarle di campi dinamici per ospitare dati flessibili.

## 🚀 Architettura e Funzionamento

Il nucleo del software ruota attorno a tre concetti principali:

### 1. Nodi (Nodes)
Il **Nodo** è l'elemento base dell'alberatura. Ogni nodo può avere un nodo "Padre" (creando così la struttura gerarchica) e infiniti "Figli".
I nodi non contengono solo un banale "titolo", ma sono arricchiti da un *Tipo di Dato* che gli conferisce proprietà specifiche. 
Tramite il **Nodes Manager** (Gestione Nodi) è possibile:
- Esplorare l'albero visivamente in un'interfaccia ad albero navigabile.
- Creare, spostare e organizzare i nodi in drag&drop (o tramite appositi modali).
- Importare interi rami di dati da file XML compatibili con **TreeLine**.

### 2. Tipi di Dati (Data Types)
Ogni Nodo è associato a un **Data Type** (Tipo di Dati). Questo rappresenta la "categoria" o "classe" del nodo.
Un Data Type definisce la natura dell'informazione che stiamo archiviando (es. `Siti`, `Account`, `Server`, `Documenti`, ecc.).
Inoltre, in un Data Type è possibile specificare un *Default Child Type*, utile a suggerire automaticamente di che tipo dovrà essere un nodo figlio quando ne viene creato uno all'interno.

### 3. Campi Dinamici (Fields)
Ogni Data Type è corredato da uno o più **Fields** (Campi). I campi definiscono la "struttura dati" (scheletro) che un nodo di quel tipo dovrà ospitare.
L'amministratore dell'applicazione può creare infiniti campi, associando loro un nome, un'etichetta (Label) e regole di validazione, e assegnarli ai vari Data Types.
*In poche parole: PrismaDataTree funge da vero e proprio Headless CMS gerarchico dove la struttura dei dati non è fissa nel database, ma definita a runtime dall'utente stesso!*

---

## 🔍 Il Motore di Ricerca (Sidebar)

Una delle funzionalità core del progetto è il potente motore di ricerca integrato direttamente nella sidebar della Gestione Nodi.
Il Search Engine permette ricerche granulari:

**Scope (Ambito):**
- **Titles only:** Cerca unicamente nei Nodi.
- **Titles + Details:** Estende la ricerca non solo ai titoli dei nodi, ma perlustra in profondità il contenuto dinamico e i valori testuali che essi contengono nei loro infiniti campi (Fields).

**Metodo di Ricerca:**
- **Key words:** Cerca le singole parole separate in modo elastico.
- **Exact full words:** Cerca parole esatte rispettando i delimitatori.
- **Exact phrase:** Cerca una frase esatta complessa.
- **Regular expression:** Usa la potenza delle espressioni regolari (RegEx) per pattern matching complessi avanzati sui dati immagazzinati.

Una volta trovato un match, è possibile scorrere (Prev / Next) ciclicamente tra tutti i risultati rintracciati evidenziandoli visivamente nell'albero.

---

## 🌍 Multilingua (Localizzazione)

Il pannello Filament è **completamente tradotto** ed è dotato di un *Language Switcher* nella topbar per passare tra le seguenti lingue in modo rapido (senza ricaricare le sessioni di autenticazione):
- 🇮🇹 **Italiano** (Lingua di default principale)
- 🇬🇧 **English** (Fallback & lingua secondaria)

Questa feature traduce etichette per Data Types, Fields, Settings, ma anche gli elementi complessi all'interno delle tabelle e dei form.

---

## 💻 Tech Stack & Sviluppo

- **Core & Framework API:** Laravel 12.x
- **Admin Panel & Interfacce:** FilamentPHP 5.x / Livewire 4.x
- **Ambiente Locale:** [DDEV](https://ddev.com/) (MySQL/MariaDB, PHP 8.3+)
- **Stile:** Tailwind CSS (v4 standard Filament / JIT compilato)

*Progetto locale privato. Cartelle di configurazione (es. `.ddev`, `.github`, ecc.) ignorate da repository origin per scopi di deployment standard.*
