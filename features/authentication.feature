Feature: Authentication
  As a user
  I want to register and login
  So that I can access protected routes

  Scenario: Register a new user
    Given I send a POST request to "/api/user/register" with:
      | email    | password      |
      | test@example.com | securepassword |
    Then the response code should be 201
    And the response should contain:
      | message | User registered successfully |
      | token   | not null                     |

  Scenario: Login with the registered user
    Given I send a POST request to "/api/user/login" with:
      | email    | password      |
      | test@example.com | securepassword |
    Then the response code should be 200
    And the response should contain:
      | message | Login successful          |
      | token   | not null                  |

  Scenario: Access a protected route
    Given I have a valid JWT token
    When I send a GET request to "/api/protected" with the token
    Then the response code should be 200
    And the response should contain:
      | message | Welcome to the protected route! |
      | user    | test@example.com                |
