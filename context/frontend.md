. Główny Układ Interfejsu (na podstawie schematu)
Interfejs użytkownika będzie składał się z kilku kluczowych obszarów, zaprojektowanych z myślą o responsywności i nowoczesnym wyglądzie dzięki Tailwind CSS:

Nawigacja Kolejkami (Górny Pasek):

Wyświetlana nazwa aktualnie wybranej kolejki (np. "Kolejka 1"), stylizowana czytelnie.

Przyciski "Poprzednia kolejka" i "Następna kolejka" do przełączania się między widokami. Przyciski będą miały wyraźny hover i focus state.

Backend będzie dostarczał listę kolejek posortowaną wg priorytetu, która może być również dostępna w formie rozwijanego menu (dropdown) dla szybszej nawigacji przy dużej liczbie kolejek.

Główny Widok Kalendarza (Centralna Część):

Implementowany przy użyciu biblioteki React Big Calendar (lub podobnej, np. FullCalendar), której domyślne style zostaną dostosowane/nadpisane za pomocą klas Tailwind CSS, aby zapewnić spójność wizualną.

Domyślnie będzie wyświetlał widok tygodniowy (Big calendar widok tygodnia). Użytkownik będzie mógł nawigować między tygodniami/dniami za pomocą wbudowanych kontrolek kalendarza.

W kalendarzu będą wyświetlane istniejące wpisy grafiku (przypisani agenci do slotów) dla wybranej kolejki i zakresu dat. Każdy wpis (wydarzenie w kalendarzu) będzie zawierał np. imię agenta i nazwę kolejki. Wygląd wydarzeń (np. kolor tła) może sygnalizować status wpisu (np. "Zaproponowany", "Potwierdzony").

Panel Boczny/Kontekstowy (Lewa Strona):

Dynamicznie pojawiający się panel, gdy manager zaznaczy konkretny slot czasowy w kalendarzu.

Sekcja "Proponowani/Dostępni Pracownicy":

Lista agentów, którzy mogliby pracować w tym slocie na wybranej kolejce.

Każdy element listy będzie zawierał imię i nazwisko agenta, wyraźną informację o jego dostępności (is_available) oraz, w przypadku niedostępności, powód (availability_conflict_reason, np. "Na urlopie", "Już pracuje na innej kolejce").

Może tu być też wyświetlona bardzo uproszczona, symboliczna metryka "wydajności" agenta (np. ikona, kolorowy wskaźnik).

Przy każdym dostępnym agencie znajdzie się przycisk "Przydziel" (lub podobny).

Opcje Filtrowania/Widoku:

Checkbox (lub przełącznik toggle) "Pokaż także niedostępnych" (lub podobna nazwa): Umożliwi managerowi włączenie/wyłączenie widoku agentów, którzy spełniają kryteria umiejętności, ale są z jakiegoś powodu niedostępni. Przy każdym takim agencie pojawi się powód niedostępności.

3. Kluczowe Interakcje Użytkownika (Manager)
Wybór Kolejki i Zakresu Dat:

Manager wybiera kolejkę za pomocą przycisków nawigacyjnych lub dropdowna.

Manager wybiera interesujący go tydzień/dzień w kalendarzu, używając kontrolek komponentu kalendarza.

Akcja: Frontend wysyła żądanie GET /api/calendar-view do backendu, aby pobrać istniejące wpisy grafiku dla wybranej kolejki i zakresu dat. Kalendarz jest aktualizowany.

Zaznaczenie Slotu Czasowego w Kalendarzu:

Manager klika na konkretny slot czasowy w kalendarzu (pusty lub z istniejącym wpisem, który chce edytować).

Akcja:

Jeśli slot jest pusty lub możliwy do edycji, otwiera się/aktualizuje Panel Boczny.

Frontend wysyła żądanie GET /api/slot-proposals z queue_id, slot_start_datetime, slot_end_datetime.

W Panelu Bocznym wyświetla się lista agentów (suggested_agents) z informacją o ich dostępności, ewentualnych konfliktach i uproszczonej metryce wydajności.

Przypisywanie Agenta do Slotu przez Managera:

Manager, widząc listę w Panelu Bocznym, klika przycisk "Przydziel" przy wybranym, dostępnym agencie.

Akcja:

Frontend wysyła żądanie POST /api/schedules z danymi agenta, kolejki i slotu.

Backend waliduje możliwość przypisania.

Jeśli sukces: Backend zwraca nowo utworzony obiekt wpisu grafiku. Frontend odświeża dane kalendarza (wołając ponownie GET /api/calendar-view lub aktualizując stan lokalnie). Panel Boczny może zostać zamknięty lub zaktualizowany.

Jeśli błąd (np. agent w międzyczasie stał się niedostępny, konflikt): Backend zwraca błąd. Frontend wyświetla stosowny, czytelny komunikat managerowi (np. toast notification lub wiadomość w panelu).

Modyfikacja Istniejącego Wpisu w Grafiku:

Manager klika na istniejące wydarzenie w kalendarzu.

W Panelu Bocznym (lub w modalu) pojawiają się opcje edycji, np. zmiana agenta, zmiana statusu, usunięcie.

Akcja (zmiana agenta): Podobna do przypisywania, ale wysyłane jest żądanie PUT /api/schedules/{schedule_entry_id}.

Akcja (zmiana statusu, np. z "Zaproponowany" na "Potwierdzony"): Frontend wysyła PUT /api/schedules/{schedule_entry_id} ze zaktualizowanym statusem.

Usuwanie Wpisu z Grafiku:

Manager wybiera opcję usunięcia przy wpisie (np. w Panelu Bocznym lub menu kontekstowym wydarzenia).

Akcja: Frontend wysyła DELETE /api/schedules/{schedule_entry_id}. Po sukcesie frontend odświeża dane kalendarza.

[Opcjonalnie] Uruchamianie Automatycznego Generowania Grafiku:

Manager może mieć dostęp do przycisku "Zaproponuj Grafik dla Tygodnia/Dnia" (w zależności od zakresu prototypu).

Akcja: Frontend wysyła żądanie POST /api/schedules/generate (ten endpoint w wersji prototypowej będzie zwracał bardzo proste, mockowe dane lub wywoływał bardzo uproszczoną logikę na backendzie). Po otrzymaniu odpowiedzi, kalendarz jest odświeżany.

4. Stylizacja i Wygląd (Tailwind CSS)
Cały interfejs będzie stylizowany przy użyciu Tailwind CSS, co zapewni szybki rozwój, spójny i nowoczesny wygląd oraz responsywność.

Elementy takie jak przyciski, panele, nagłówki, czcionki, marginesy i paddingi będą definiowane za pomocą klas użytkowych Tailwind.

Komponent kalendarza (np. React Big Calendar) będzie miał swoje domyślne style dostosowane lub nadpisane przez klasy Tailwind, aby wpasować się w ogólny design aplikacji.

Kolorystyka będzie stonowana i profesjonalna, z wyraźnym wskazaniem elementów interaktywnych i statusów (np. różne kolory dla wydarzeń w kalendarzu w zależności od entry_status).

5. Komponenty React (Przykładowa Struktura)
Aplikacja będzie podzielona na logiczne komponenty, np.:

App.js: Główny komponent aplikacji.

Layout.js: Komponent definiujący ogólny układ strony (np. z górnym paskiem nawigacyjnym i główną treścią).

QueueNavigator.js: Komponent dla górnego paska z wyborem kolejki.

ScheduleCalendarView.js: Główny komponent zawierający React Big Calendar i logikę pobierania/wyświetlania danych grafiku.

SlotProposalPanel.js: Komponent dla bocznego panelu z listą proponowanych agentów.

AgentListItem.js: Komponent dla pojedynczego agenta na liście propozycji.

ConfirmationModal.js / NotificationToast.js: Komponenty do wyświetlania potwierdzeń i powiadomień.

6. Zarządzanie Stanem (Frontend)
Do zarządzania globalnym stanem aplikacji (np. aktualnie wybrana kolejka, zakres dat, dane grafiku, lista propozycji agentów, stan ładowania) zostanie wykorzystany React Context API. Lokalny stan komponentów (useState, useReducer) będzie używany tam, gdzie to stosowne dla UI specyficznych elementów.

Dane pobierane z API będą przechowywane w tym globalnym stanie (zarządzanym przez Context API) i udostępniane komponentom, które ich potrzebują.

HOTFIX! DO QUEUES TRZEBA DODAĆ   `target_handled_calls_per_slot` INT NULL COMMENT 'Docelowa liczba obsłużonych połączeń na slot czasowy dla tej kolejki',
    `target_success_rate_percentage` DECIMAL(5,2) NULL COMMENT 'Docelowy procent połączeń zakończonych sukcesem (np. 90.50 dla 90.5%)',
  
