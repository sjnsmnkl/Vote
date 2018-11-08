<?php
    
    namespace Drupal\vote_for_event\Controller;

    class VoteForEventController {
        public function vote() {
            return array(
                '#title' => "Vote for Next Year's Event",
                '#markup' => "Vote for Next Year's Event"
            );
        }
}
