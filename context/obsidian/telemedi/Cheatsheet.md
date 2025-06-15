# OBSIDIAN: CHEAT SHEET (DLA ARCHITEKTÓW, NIE POETÓW)
## Protokół: `GHOST_DEVELOPER_V5.0`

---

### I. FUNDAMENTY (TERMINAL)

- **Nowa Notatka:** `Ctrl + N`
- **Nowy Folder:** Ikona w panelu bocznym.
- **Paleta Komend:** `Ctrl + P` (lub `Cmd + P`). Twoje centrum dowodzenia. Używaj tego do wszystkiego.

---

### II. CANVAS (TWOJE ŚRODOWISKO PROJEKTOWE)

- **Stwórz Nowy Canvas:**
  - `Ctrl + P` -> wpisz "Canvas" -> wybierz `Create new canvas`.
- **Dodaj Kartę (Węzeł):**
  - Przeciągnij istniejący plik `.md` z panelu bocznego na płótno.
  - **LUB:** Kliknij dwukrotnie na pustym miejscu, żeby stworzyć nową kartę-notatkę.
- **Połącz Karty (Krawędź):**
  - Złap za kółko na krawędzi jednej karty i przeciągnij strzałkę do drugiej. Proste. Logiczne.
- **Dodaj Etykiety do Połączeń:**
  - Kliknij dwukrotnie na linii łączącej, żeby dodać opis (np. "wysyła żądanie do", "dziedziczy z").

---

### III. LINKOWANIE WEWNĄTRZ NOTATEK (RDZEŃ SIECI)

- **Link do Innej Notatki:** `[[Nazwa Notatki]]`
  - *Standard. Znasz to.*
- **Link z Aliasem:** `[[Nazwa Notatki|Mój własny tekst linku]]`
  - *Użyteczne, żeby linki brzmiały naturalnie w tekście.*
- **Link do Nagłówka:** `[[Nazwa Notatki#Nazwa Nagłówka]]`
  - *Precyzyjne celowanie. Niezbędne w długich dokumentach.*
- **Link do Bloku:** `[[Nazwa Notatki#^ID_Bloku]]`
  - **Jak to działa:**
    1. Idź do linijki (bloku), do której chcesz linkować.
    2. Na końcu tej linijki, dopisz ` ^` (spacja i daszek).
    3. Obsidian automatycznie wygeneruje unikalne ID (np. `^1a2b3c`).
    4. Teraz, w innej notatce, zacznij pisać `[[Nazwa Notatki#^` a Obsidian sam podpowie ci dostępne bloki.
  - *To jest twój sposób na tworzenie **atomowych, niepodważalnych połączeń** między konkretnymi fragmentami logiki.*

---

### IV. EKSPORT DLA AI (FAZA KOMPILACJI)

- **Twój Cel:** Wygenerować plik `.canvas`, który jest twoją specyfikacją.
- **Proces:**
  1. Stwórz swój diagram w Canvas.
  2. Zapisz go.
  3. Znajdź plik `.canvas` w swoim folderze systemowym.
  4. Otwórz go w edytorze tekstu, żeby zobaczyć jego strukturę (opartą na JSON).
  5. Przekaż ten plik (lub jego treść) do modelu AI z precyzyjną dyrektywą.