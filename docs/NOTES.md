# Notatki z realizacji zadania

## 1. Instalacja
Pobrałem pliki, przejrzałem pobieżnie kod źródłowy (zlokalizowałem od razu kilka problemów, które będę realizował w kolejnych krokach). Odpaliłem lokalnie oba API. Podpiąłem do PHPStorm bazy danych i zweryfikowałem dostępy.

## 2. Publikacja repo
Opublikowałem repo na GitHubie w publicznym repo (zgodnie z poleceniem z maila). Kwestię publikacji haseł w repo zostawiłem bez zmian, gdyż są to hasła developerskie i nie stanowią zagrożenia. Normalnie hasła bym trzymał w .env, którego bym nie publikował w repo, a dodał jedynie .env.exmple

## 3. AuthController
Odkryłem istotne podaności i błędy:
- podatność na SQL Injection
- błąd w zapytaniu SQL, który nie sprawdza czy token należy do właściwego użytkownika
- podatność na enumeracje bazy danych (przez weryfikacje statusów HTTP)
- podatność na session fixation
- zbyt duża odpowiedzialność klasy

### 3.1. Czego nie poprawiam, choć warto to rozważyć
- nie zmieniam logiki przekazywania username i tokenu w URL - nie jest to bezpieczne
- nie dodaje sprawdzania ważności tokenu
- nie unieważniam tokenu 

### 3.2. Naprawa istotnych błędów
W pierwszej kolejności utworzyłem test funkcjonalny, który potwierdza błędy, a następnie dokonałem ich szybkiej poprawy, ale bez większego refaktoru. Uznałem, że naprawa jest priorytetowa, a refaktor wykonam w kolejnych etapach.