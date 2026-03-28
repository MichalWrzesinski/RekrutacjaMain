# Notatki z realizacji zadania

## 1. Instalacja
Pobrałem pliki, przejrzałem pobieżnie kod źródłowy (zlokalizowałem od razu kilka problemów, które będę realizował w kolejnych krokach). Odpaliłem lokalnie oba API. Podpiąłem do PHPStorm bazy danych i zweryfikowałem dostępy.

## 2. Publikacja repo
Opublikowałem repo na GitHubie w publicznym repo (zgodnie z poleceniem z maila). Kwestię publikacji haseł w repo zostawiłem bez zmian, gdyż są to hasła developerskie i nie stanowią zagrożenia. Normalnie hasła bym trzymał w `.env`, którego bym nie publikował w repo, a dodał jedynie `.env.exmple`

## 3. Zadanie nr 1
Podsumowanie: zweryfikowałem główne problemy - skupiłem się głównie na `AuthController`, gdzie dokonałem istotnych poprawek, refaktoryzacji oraz utworzyłem testy. Pozostałe klasy przejrzałem - częściowo poprawiłem, częściowo tylko wypisałem uwagi. Części plików nie dotykałem, gdyż mam ograniczony czas na wykonanie zadania rekrutacyjnego. Natomiast w każdym miejscu szedłbym tą samą drogą: najpierw napisanie testów, potem poprawa istotnych błędów, a na końcu refaktoryzacja.

### 3. AuthController
Odkryłem istotne podatności i błędy:
- podatność na `SQL Injection`
- błąd w zapytaniu SQL, który nie sprawdza, czy token należy do właściwego użytkownika
- podatność na enumeracje bazy danych (przez weryfikacje statusów `HTTP`)
- podatność na `session fixation`
- zbyt duża odpowiedzialność klasy

#### 3.1. Czego nie poprawiam, choć warto to rozważyć
- nie zmieniam logiki przekazywania username i tokenu w URL - nie jest to bezpieczne
- nie dodaje sprawdzania ważności tokenu
- nie unieważniam tokenu 
- nie zmieniłem ustawień w `security.yaml`, które wyłączają `Symfony Security` - nie jest to dobra praktyka

#### 3.2. Naprawa istotnych błędów
W pierwszej kolejności utworzyłem test funkcjonalny, który potwierdza błędy, a następnie dokonałem ich szybkiej poprawy, ale bez większego refaktoru. Uznałem, że naprawa jest priorytetowa, a refaktor wykonam w kolejnych etapach.

#### 3.3. Refaktor
Wydzieliłem z kontrolera logikę, tak aby każda klasa odpowiadała za wybraną część kodu. Utworzyłem `Service`, `Query`, `Exception`, `Dto`. Dodałem testy jednostkowe i funkcjonalne.

### 4. HomeController
- odkryłem i poprawiłem problemy z wiązane z przypisywaniem użytkownika w każdym cyklu pętli
- poprawiłem brak wykorzystania `Dependency Injection` dla `PhotoRepository` i `LikeRepository`
- poprawiłem deklaracje `route` i usunąłem błędny `PHPdoc`

#### 4.1. Czego nie poprawiam, choć warto to rozważyć
- nie wykonałem refaktoru kontrolera i wydzielenia logiki, a co za tym idzie nie tworzyłem nowych testów

### 5. Pozostałe klasy, które weryfikowałem, ale już bez poprawiania

#### 5.1. PhotoController
- brak wykorzystania `Dependency Injection`
- za dużo logiki w kontrolerze - wymaga wydzielenia
- brak metody w konfiguracji `route`
- klasa mogłaby być `final`
- brak pełnego typowania mimo deklaracji `strict_types`
- brak sprawdzenia, czy użytkownik istnieje i ogólnie brak walidacji

#### 5.2. Inne uwagi zbiorcze
- użycie `#[\Override]` w `LikeRpository`, które nie jest dostępne w PHP 8.1 (doszło w PHP 8.3). PHP zinterpretuje to jako zwykły atrybut
- chaos w katalogu Likes - można by lepiej zorganizować kod
- encje są nieprecyzyjnie zdefiniowane (bywają rozjazdy typowaniu)

Na tym etapie kończę zadanie nr 1 i przechodzę do kolejnych.

## 4. Zadanie nr 2
- dodanie pola `phoenixApiToken` w encji `User` (+migracja)
- do pliku z szablonem twig dodałem formularz zapisu tokena (można by tam ogarnąć CSS, żeby nie duplikować wspólnych elementów styli)
- refaktor `ProfileController` (dodanie `UserRepository`, sprawdzanie `CSRF` tokena, wydzielenie obsługi sesji do resolvera `SessionUserResolver`, wydzielenie logiki zapisu do nowego serwisu `SavePhoenixTokenService`)
- dodałem testy resovlera i zapisu tokena

### 4.1. 4.1. Czego nie poprawiam, choć warto to rozważyć
- nie przeszedłem na lepsze zarządzanie użytkownikami (np. użycie komponentu `Security` od `Symfony`)
- nie tworzyłem testów funkcjonalnych dla `ProfileController`