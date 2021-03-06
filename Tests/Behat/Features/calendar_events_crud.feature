@regression
Feature: Create calendar events
  In order to have my company events organized
  As Admin user
  I need to be able to create events with different recurrences

  Scenario: Create AllDay No-repeat calendar event
    Given I login as administrator
    And go to Activities/ Calendar Events
    And press "Create Calendar event"
    When I save and close form
    Then I should see validation errors:
      | Title | This value should not be blank. |
    When I fill "Event Form" with:
      | Title         | All day no repeat Event |
      | Start         | 2017-01-24 12:00 AM     |
      | End           | 2020-02-26 12:00 AM     |
      | All-Day Event | true                    |
      | Description   | testfull desc           |
      | Guests        | John Doe                |
      | Color         | Cornflower Blue         |
    And set Reminders with:
      | Method        | Interval unit | Interval number |
      | Email         | days          | 1               |
      | Flash message | minutes       | 30              |
    And I save and close form
    And press "Notify"
    Then I should see "Calendar event saved" flash message
    When I click My Calendar in user menu
    Then I should see "All day no repeat Event" in calendar with:
      | Description   | testfull desc                |
      | Guests        | John Doe (admin@example.com) |
      | All-day event | Yes                          |

  Scenario: Create daily weekday never ending Event
    When I go to Activities/ Calendar Events
    And press "Create Calendar event"
    Then I fill "Event Form" with:
      | Title           | Daily weekday never ending Event |
      | Start           | <DateTime:today>                 |
      | End             | <DateTime:today +2 month>        |
      | Description     | testfull desc                    |
      | Repeats         | Daily                            |
      | DailyRecurrence | Repeat every:Weekday             |
    And I save and close form
    Then I should see "Calendar event saved" flash message
    When I click My Calendar in user menu
    And I go to next calendar page
    Then I should see "Daily weekday never ending Event" in calendar with:
      | Description   | testfull desc        |
      | All-day event | No                   |
      | Recurrence    | Daily, every weekday |

  Scenario: Create Daily every 3 days, after 5 occurrences ending Event
    When I go to Activities/ Calendar Events
    And press "Create Calendar event"
    Then I fill "Event Form" with:
      | Title           | Three days five occ ending Event |
      | Start           | <DateTime:today>                 |
      | End             | <DateTime:next month>            |
      | Description     | every 3 days                     |
      | Repeats         | Daily                            |
      | DailyRecurrence | Repeat every:3 days              |
      | EndsRecurrence  | After:5                          |
    And I save and close form
    Then I should see "Calendar event saved" flash message
    When I click My Calendar in user menu
    Then I should see "Three days five occ ending Event" in calendar with:
      | Description   | every 3 days                                |
      | All-day event | No                                          |
      | Recurrence    | Daily every 3 days, end after 5 occurrences |

  Scenario: Create Daily every 5 days, by next month ending Event
    When I go to Activities/ Calendar Events
    And press "Create Calendar event"
    Then I fill "Event Form" with:
      | Title           | Two days by month ending Event |
      | Start           | <DateTime:today>               |
      | End             | <DateTime:next week>           |
      | Description     | every 5 days                   |
      | Repeats         | Daily                          |
      | DailyRecurrence | Repeat every:5 days            |
      | EndsRecurrence  | By:next month                  |
    And I save and close form
    Then I should see "Calendar event saved" flash message
    When I click My Calendar in user menu
    Then I should see "Two days by month ending Event" in calendar with:
      | Description   | every 5 days                            |
      | All-day event | No                                      |
      | Recurrence    | Daily every 5 days, end by <Date:next month> |

  Scenario: Create weekly, every 2 weeks on Monday, 2 occ ending Event
    When I go to Activities/ Calendar Events
    And press "Create Calendar event"
    Then I fill "Event Form" with:
      | Title            | Two weeks two occ ending Event         |
      | Start            | <DateTime:today>                       |
      | End              | <DateTime:next month>                  |
      | Description      | every 2 weeks on mondays               |
      | Repeats          | Weekly                                 |
      | WeeklyRecurrence | Repeat every:2 weeks, Repeat on:monday |
      | EndsRecurrence   | After:2                                |
    And I save and close form
    Then I should see "Calendar event saved" flash message
    And I should see Three weeks never ending Event with:
      | Description   | every 2 weeks on mondays                                |
      | All-day event | No                                                      |
      | Recurrence    | Weekly every 2 weeks on Monday, end after 2 occurrences |

  Scenario: Create weekly, every 3 weeks never ending Event
    When I go to Activities/ Calendar Events
    And press "Create Calendar event"
    Then I fill "Event Form" with:
      | Title            | Three weeks never ending Event         |
      | Start            | <DateTime:today>                       |
      | End              | <DateTime:next month>                  |
      | Description      | every 3 weeks                          |
      | Repeats          | Weekly                                 |
      | WeeklyRecurrence | Repeat every:3 weeks, Repeat on:sunday |
      | EndsRecurrence   | Never                                  |
    And I save and close form
    Then I should see "Calendar event saved" flash message
    And I should see Three weeks never ending Event with:
      | Description   | every 3 weeks                  |
      | All-day event | No                             |
      | Recurrence    | Weekly every 3 weeks on Sunday |

  Scenario: Create Monthly First Weekday next year ending Event
    When I go to Activities/ Calendar Events
    And press "Create Calendar event"
    Then I fill "Event Form" with:
      | Title             | Monthly First Weekday by ny ending Event |
      | Start             | <DateTime:today>                         |
      | End               | <DateTime:next month>                    |
      | Description       | every First Weekday of month             |
      | Repeats           | Monthly                                  |
      | MonthlyRecurrence | Repeat on:First Weekday                  |
      | EndsRecurrence    | By:next year                             |
    And I save and close form
    Then I should see "Calendar event saved" flash message
    And I should see Monthly First Weekday by ny ending Event with:
      | Description   | every First Weekday of month                                   |
      | All-day event | No                                                             |
      | Recurrence    | Monthly the first weekday of every 1 month, end by <Date:next year> |

  # TODO: unskip when bug related to BB-8622 fixed
  @skip
  Scenario: Create yearly April Day 1, 5 recurrence ending Event
    When I go to Activities/ Calendar Events
    And press "Create Calendar event"
    Then I fill "Event Form" with:
      | Title            | Yearly April Day one rcr five ending Event  |
      | Start            | <DateTime:today>                            |
      | End              | <DateTime:next year>                        |
      | Description      | Yearly April Day one recurrence five ending |
      | Repeats          | Yearly                                      |
      | YearlyRecurrence | Repeat on:April First Day                   |
      | EndsRecurrence   | After:5                                     |
    And I save and close form
    Then I should see "Calendar event saved" flash message
    And I should see Yearly April Day one rcr five ending Event with:
      | Description   | Yearly April Day one recurrence five ending                          |
      | All-day event | No                                                                   |
      | Recurrence    | Yearly every 1 year on the first day of Apr, end after 5 occurrences |

  Scenario: Edit yearly April Day 1, 5 recurrence ending Event
    When I press "Edit Calendar event"
    And I fill "Event Form" with:
      | Title            | New year Event                                |
      | Start            | <DateTime:today>                              |
      | End              | <DateTime:+3 years>                           |
      | Description      | Yearly January Day one recurrence five ending |
      | Repeats          | Yearly                                        |
      | YearlyRecurrence | Repeat on:January First Day                   |
      | EndsRecurrence   | Never                                         |
    And I save and close form
    Then I should see "Calendar event saved" flash message
    And I should see New year Event with:
      | Description   | Yearly January Day one recurrence five ending |
      | All-day event | No                                            |
      | Recurrence    | Yearly every 1 year on the first day of Jan   |

  Scenario: Delete calendar event
    Given I press "Delete"
    When I confirm deletion
    Then I should see "Calendar event deleted" flash message
    And I should not see "New year Event"

  Scenario: Delete all events
    Given I go to Activities/ Calendar Events
    And I check all records in grid
    When I click "Delete" link from mass action dropdown
    And I confirm deletion
    Then I should see success message with number of records were deleted
