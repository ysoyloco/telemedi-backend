[[A_Controllers]]
[[A_Entities]]
[[A_Models]]
[[A_Services]]

1. Główne Założenia Upraszczające Prototyp

Manager zna obłożenie: Zakładamy, że manager samodzielnie określa potrzebne obłożenie (przewidywane zapotrzebowanie) dla danego slotu czasowego i kolejki. System w prototypie nie będzie implementował modułu dynamicznej predykcji zapotrzebowania. Tabela demand_predictions (lub jej odpowiednik) jest pomijana.

  

Frontend First z Mockup API: Rozwój rozpoczyna się od frontendu. Backend (Symfony) początkowo dostarcza mockup API (zaszyte, statyczne odpowiedzi), aby umożliwić szybkie zbudowanie klikalnego interfejsu. Rozbudowa logiki backendu (repozytoria, bardzo prosty serwis planowania) nastąpi, jeśli starczy czasu.

  

Uproszczona logika doboru agentów: "Inteligentny Agent Planujący" w wersji prototypowej będzie miał bardzo uproszczoną logikę. Skupi się na podstawowym filtrowaniu i walidacji, a nie na zaawansowanej optymalizacji.

  

2. Kluczowe Funkcjonalności Prototypu (Uproszczone)

Interaktywny Kalendarz: Widok "Kalendarz per Kolejka" (React Big Calendar) do wyświetlania grafiku.

  

Wybór slotu i propozycja agentów: Po kliknięciu na slot, system wyświetla listę agentów, którzy mogliby pracować, wraz z informacją o ich dostępności i ewentualnych konfliktach.

  

Przypisywanie/Modyfikacja agenta: Manager może przypisać agenta do slotu. System waliduje tę operację.

  

Dynamiczna, uproszczona ocena wydajności: Efektywność agenta jest obliczana "w locie" w bardzo prosty sposób na podstawie danych z agent_activity_log (która musi być wstępnie wypełniona dla celów demo) lub jest to stała, przykładowa wartość.

  

Sortowanie kolejek: Lista kolejek do wyboru jest sortowana wg priorytetu.

  

3. Uproszczona Struktura Bazy Danych (MySQL)

Bazujemy na dostarczonym przez Ciebie dump.sql. Kluczowe tabele to:

  

agents: Dane agentów, ich standardowa dostępność (default_availability_pattern), status aktywności.

  

queues: Definicje kolejek, ich priorytety.

  

agent_skills: Tabela łącząca, wskazująca, które kolejki agent potrafi obsłużyć.

  

agent_availability_exceptions: Urlopy, zwolnienia i inne planowane niedostępności.

  

agent_activity_log: Centralna tabela dla danych historycznych. Służy jako podstawa do bardzo prostej estymacji zapotrzebowania (jeśli zdecydujemy się to symulować) i dynamicznego obliczania uproszczonej metryki wydajności agenta. Dla prototypu tabela ta musi być wstępnie wypełniona danymi.

  

schedules: Wygenerowane i potwierdzone wpisy grafiku, zawiera entry_status.

  

queue_load_trends: Tabela analityczna, zdefiniowana w schemacie, może zawierać statyczne dane przykładowe; mechanizm jej aktualizacji nie będzie implementowany.

  

Pominięta tabela (w stosunku do wcześniejszych, bardziej złożonych koncepcji): demand_predictions.

  

4. Przepływ Użytkownika i Interakcja z API (Uproszczony)

Inicjalizacja Widoku Kalendarza:

  

Frontend pobiera listę kolejek: woła GET /api/queues.

  

Frontend pobiera dane do wyświetlenia w kalendarzu dla domyślnego/wybranego zakresu dat i kolejki: woła GET /api/calendar-view.

  

Zmiana Kolejki lub Zakresu Dat:

  

Użytkownik wybiera inną kolejkę lub zmienia zakres dat.

  

Frontend ponownie woła GET /api/calendar-view z nowymi parametrami.

  

Zaznaczenie Slotu Czasowego w Kalendarzu:

  

Użytkownik klika na konkretny slot (pusty lub z istniejącym wpisem).

  

Frontend woła GET /api/slot-proposals z queue_id, slot_start_datetime, slot_end_datetime.

  

W UI (np. w panelu bocznym) wyświetla się lista agentów (suggested_agents) z informacją o ich dostępności i ewentualnych konfliktach, oraz bardzo prostą metryką wydajności.

  

Przypisywanie Agenta do Slotu przez Managera:

  

Manager klika na wybranego agenta z listy propozycji.

  

Frontend wysyła żądanie POST /api/schedules (dla nowego przypisania).

  

Backend (nawet w wersji mockup) próbuje "zapisać" wpis, wykonując podstawową walidację (np. czy agent nie jest już zajęty w tym slocie w tabeli schedules).

  

Walidacja przed zapisem: Backend sprawdza:

  

Dostępność ogólną agenta (z agents.default_availability_pattern i agent_availability_exceptions).

  

Konflikt z innym wpisem w schedules dla tego agenta w tym samym czasie.

  

Odpowiedź:

  

Jeśli sukces: Backend zwraca nowo utworzony obiekt wpisu grafiku. Frontend aktualizuje kalendarz.

  

Jeśli błąd (np. agent niedostępny, konflikt): Backend zwraca błąd z kodem i komunikatem (reason_code, message). Frontend wyświetla stosowną informację managerowi.

  

Modyfikacja Istniejącego Wpisu:

  

Manager edytuje istniejący wpis (np. zmienia agenta, status).

  

Frontend wysyła PUT /api/schedules/{schedule_entry_id}.

  

Logika walidacji i odpowiedzi analogiczna jak przy POST.

  

Usuwanie Wpisu:

  

Manager usuwa wpis.

  

Frontend wysyła DELETE /api/schedules/{schedule_entry_id}.

  

Po sukcesie frontend odświeża kalendarz.

  

5. Uproszczone Endpointy API (Backend PHP/Symfony - Mockup)

Poniższe endpointy są zaprojektowane z myślą o maksymalnym uproszczeniu logiki backendu na potrzeby prototypu.

  

1. Pobieranie Listy Kolejek

GET /api/queues

  

Cel: Pobranie listy wszystkich dostępnych kolejek.

  

Request (Query Params): Brak lub opcjonalnie sort_by=priority:asc (backend domyślnie sortuje po priorytecie).

  

Response (JSON): Tablica obiektów kolejek.

  

[

{ "id": 1, "queue_name": "Sprzedaż VIP", "priority": 1 },

{ "id": 2, "queue_name": "Wsparcie Techniczne", "priority": 2 }

]

  

2. Pobieranie Danych dla Widoku Kalendarza

GET /api/calendar-view

  

Cel: Pobiera istniejące wpisy grafiku dla określonego zakresu dat i kolejki.

  

Request (Query Params):

  

start_date (wymagany, np. 2025-06-09)

  

end_date (wymagany, np. 2025-06-15)

  

queue_id (wymagany)

  

Response (JSON):

  

{

"queue_info": { "id": 1, "queue_name": "Sprzedaż VIP", "priority": 1 },

"schedule_entries": [

{

"id": 101, // schedule_entry_id z tabeli schedules

"agent_id": 1,

"agent_full_name": "Jan Kowalski",

"queue_id": 1,

"schedule_date": "2025-06-10",

"time_slot_start": "09:00:00",

"time_slot_end": "10:00:00",

"entry_status": "Potwierdzony_Przez_Managera", // Z tabeli schedules

// Pola dla React Big Calendar

"title": "Jan K. (Sprzedaż VIP)", // Tekst na wydarzeniu

"start": "2025-06-10T09:00:00Z", // Pełna data i czas rozpoczęcia (UTC lub z offsetem)

"end": "2025-06-10T10:00:00Z", // Pełna data i czas zakończenia

"resourceId": 1 // Może być agent_id

}

// ... więcej wpisów z tabeli schedules

]

// Usunięto "estimated_demand_per_slot" - manager wie o obłożeniu

}

  

3. Pobieranie Propozycji Agentów dla Wybranego Slotu

GET /api/slot-proposals

  

Cel: Gdy manager zaznaczy slot, backend zwraca listę agentów, którzy mogliby pracować (mają umiejętność), wraz z informacją o ich dostępności i bardzo prostą oceną wydajności.

  

Request (Query Params):

  

queue_id (wymagany)

  

slot_start_datetime (wymagany, np. 2025-06-10T09:00:00Z)

  

slot_end_datetime (wymagany, np. 2025-06-10T10:00:00Z)

  

Response (JSON):

  

{

"slot_info": {

"queue_id": 1,

"queue_name": "Sprzedaż VIP",

"slot_start_datetime": "2025-06-10T09:00:00Z",

"slot_end_datetime": "2025-06-10T10:00:00Z"

},

"suggested_agents": [ // Lista agentów, którzy mają umiejętność (z agent_skills)

{

"agent_id": 1,

"full_name": "Jan Kowalski",

"is_available": true, // Wynik sprawdzenia default_availability_pattern, exceptions i konfliktów w schedules

"availability_conflict_reason": null, // lub np. "Na urlopie", "Już pracuje na Innej Kolejce"

"simple_performance_metric": "OK" // Bardzo prosta ocena z agent_activity_log lub stała/losowa

},

{

"agent_id": 2,

"full_name": "Maria Nowak",

"is_available": false,

"availability_conflict_reason": "Urlop wypoczynkowy",

"simple_performance_metric": "Dobry"

}

]

}

  

Logika backendu (mockup/uproszczona):

  

Znajdź agentów z umiejętnością dla queue_id (z agent_skills).

  

Dla każdego z nich sprawdź dostępność w danym slot_datetime (na podstawie agents.default_availability_pattern, agent_availability_exceptions i czy nie ma już wpisu w schedules w tym czasie).

  

Zwróć bardzo prostą metrykę wydajności (np. stałą wartość, losową, lub super prostą agregację z agent_activity_log).

  

4. Tworzenie Nowego Wpisu w Grafiku

POST /api/schedules

  

Cel: Manager przypisuje agenta do slotu.

  

Request (JSON Body):

  

{

"agent_id": 1,

"queue_id": 1,

"schedule_date": "2025-06-10",

"time_slot_start": "09:00:00", // Czas lokalny

"time_slot_end": "10:00:00", // Czas lokalny

"entry_status": "Potwierdzony_Przez_Managera" // Lub inny status inicjalny

}

  

Logika backendu (mockup/uproszczona):

  

Walidacja: Sprawdź, czy agent jest dostępny (ogólnie i czy nie ma urlopu). Sprawdź, czy agent nie jest już przypisany w schedules w tym samym schedule_date i time_slot_start.

  

Jeśli walidacja przejdzie: zapisz do tabeli schedules.

  

Response (Sukces - HTTP 201 Created): Obiekt nowo utworzonego wpisu grafiku (jak w GET /api/calendar-view).

  

Response (Błąd - HTTP 400 Bad Request lub HTTP 409 Conflict):

  

{

"error": "Nie można przypisać agenta.",

"reason_code": "AGENT_UNAVAILABLE_GENERAL", // Lub AGENT_ALREADY_SCHEDULED_CONFLICT

"message": "Agent Jan Kowalski jest niedostępny w tych godzinach."

}

  

5. Aktualizacja Istniejącego Wpisu w Grafiku

PUT /api/schedules/{schedule_entry_id}

  

Cel: Manager modyfikuje istniejący wpis (np. zmienia agenta, status).

  

Request (JSON Body): Pola do aktualizacji, np.:

  

{

"agent_id": 2, // Np. zmiana agenta

"entry_status": "Potwierdzony_Przez_Managera_Po_Zmianie"

}

  

Logika backendu: Analogiczna walidacja jak przy POST jeśli zmieniany jest agent/czas. Aktualizacja wpisu w tabeli schedules.

  

Response (Sukces - HTTP 200 OK): Zaktualizowany obiekt wpisu grafiku.

  

Response (Błąd): Analogicznie jak przy POST.

  

6. Usuwanie Wpisu z Grafiku

DELETE /api/schedules/{schedule_entry_id}

  

Cel: Usunięcie wpisu.

  

Response: HTTP 204 No Content (sukces).