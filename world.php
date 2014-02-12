<?php
Class World {
  private $population;
  private $changed;

  private $width;
  private $height;

  private $social_unrest;
  private $social_speed;
  private $social_acceleration;

  private $generation;

  public function __construct($wide, $high) {
    $this->width = $wide;
    $this->height = $high;

    $this->social_unrest = array();
    $this->social_speed = array();
    $this->social_acceleration = array();

    $this->generation = 0;

    for ($w = 0; $w < $wide; $w++) {
      $this->population[] = array();
      for ($h = 0; $h < $high; $h++) {
        $this->population[$w][] = 0;
      }
    }
  }

  public function initialize_with_file($filename) {
    $world_file = fopen($filename, "r");
    $line_count = 0;
    while (!feof($world_file)) {
      $line = fgets($world_file);
      if ($line !== '') {
        $words = explode(' ', $line);
        $words_count = count($words);
        for ($x = 0; $x < $words_count; $x++) {
          if ($words[$x] === "1") {
            $this->population[$x][$line_count] = 1;
            $this->add_to_changed($x, $line_count);
          }
        }
      }
      $line_count += 1;
    }
  }

  public function next_generation() {
    $new_population = $this->population;

    $past_changed = $this->changed;

    $this_generations_unrest = count($past_changed);

    unset($this->changed);
    $this->changed = array();

    if (!empty($past_changed)) {
      foreach($past_changed as $altered) {
        $altered = explode(" ", $altered);
        $x = $altered[0];
        $y = $altered[1];
        for ($neighbor_x = $x -1; $neighbor_x <= $x + 1; $neighbor_x++) {
          for ($neighbor_y = $y -1; $neighbor_y <= $y + 1; $neighbor_y++) {
            if ($this->check_if_change_needed($neighbor_x, $neighbor_y)) {
              $new_state = ($this->population[$neighbor_x][$neighbor_y] === 1) ? 0 : 1;
              $new_population[$neighbor_x][$neighbor_y] = $new_state;
              $this->add_to_changed($neighbor_x, $neighbor_y);
            }
          }
        }
      }
    }
    unset($past_changed);
    unset($this->population);

    $this->update_social_unrest($this_generations_unrest);
    $this->changed = array_unique($this->changed);
    $this->population = $new_population;
    $this->generation += 1;
  } 

  public function add_to_changed($x, $y) {
    //$new_change = array("x" => $x, "y" => $y);
    /*
     * I know what you're thinking- why did this kid make it a string that he'll
     * explode later in the next generation step!?
     * Well, the 'array_unique' function used to trim down the changed array
     * is easily confused. It doesn't get arrays, and allows all manner of
     * duplicates. As you can imagine, if it allows even 2 duplicates in the 
     * first iteration, then it will fill up with duplicate results for those
     * duplicates, and their duplicates, and so on until my little laptop's
     * heart explodes.
     * So strings it is!
     */
    $new_change = "$x $y";
    $this->changed[] = $new_change;
  }

  public function get_neighbor_count($x, $y) {
    $total = 0;
    for ($nearby_x = $x -1; $nearby_x <= $x +1; $nearby_x++) {
      for ($nearby_y = $y -1; $nearby_y <= $y +1; $nearby_y++) {
        if ($nearby_x !== $x || $nearby_y !== $y) {
          $total += $this->population[$nearby_x][$nearby_y];
        }
      }
    }
    return $total;
  }

  public function check_if_change_needed($x, $y) {
    $state = $this->population[$x][$y];
    $neighbors = $this->get_neighbor_count($x, $y);

    if ($state === 0) {
      if ($neighbors === 3) {
        return true;
      }
      return false;
    }

    if ($state === 1) {
      if ($neighbors < 2) {
        return true;
      }
      if ($neighbors > 3) {
        return true;
      }
    }
    return false;
  }

  public function get_state_diagram() {
    $diagram = '';
    for ($y = 0; $y < $this->height; $y++) {
      for ($x = 0; $x < $this->width; $x++) {
        $state = $this->population[$x][$y];
        $diagram .= "$state ";
      }
      $diagram .= PHP_EOL;
    }
    return $diagram;
  }

  public function update_social_unrest($dissidence) {
    $this->social_unrest[] = $dissidence;

    if (count($this->social_unrest) > 1) {
      $last = count($this->social_unrest) -1;
      $speed = $this->social_unrest[$last-1] - $this->social_unrest[$last];
      $this->social_speed[] = $speed;
    }

    if (count($this->social_speed) > 1) {
      $last = count($this->social_speed) -1;
      $acc = $this->social_speed[$last-1] - $this->social_speed[$last];
      $this->social_acceleration[] = $acc;
    }
  }

  public function report($style) {
    $population_size = $this->width * $this->height;
    $unrest_percentage = (end($this->social_unrest) / $population_size) * 100;  
    $report = "---------Year " . $this->generation . " --------------------" . PHP_EOL;
    if ($style == 1) {
      $report .= "To the Illustrious Magistrate of the Hegemony of Conservation" . PHP_EOL;
      $report .= "The Only Thing Worse Than Change Is More Change";
      $percentage_report = '';
      switch (floor($unrest_percentage / 10)) {
        case 0:
          $percentage_report = "The people are overwhelmingly sedate. Our dream of a static society seems so close!";
          break;
        case 1:
          $percentage_report = "Almost every citizen supports the efforts of the government. Our rule is one without a forseeable end!";
          break;
        case 2:
          $percentage_report = "More than one in five citizens are against us! This is somewhat worrying, sir.";
          break;
        case 3:
          $percentage_report = "Some of our citizens are distributing anti-government proppganda. Like good books, and cool pictures. This cannot last!";
          break;
        case 4:
          $percentage_report = "Almost half of our citizens resent the clarity and vision that the government provides for them. They are ingrates! Our intellectual superiority will win out.";
          break;
        case 5:
          $percentage_report = "A civil war has broken out. Most of our civilians have defected, but many still believe in the lies-- uh, I mean, awesome ideas, that we espouse. Listen, I, uh, have a doctor's appointment that I'll be at for a few months.";
          break;
        case 6:
          $percentage_report = "A civil war has broken out. Most of our civilians have defected, but many still believe in the lies-- uh, I mean, awesome ideas, that we espouse. Listen, I, uh, have a doctor's appointment that I'll be at for a few months.";
          break;
        case 7:
          $percentage_report = "Well, the good news about this social situation is that we have really STRONG bunkers.";
          break;
        case 8:
          $percentage_report = "Boy, sure is cool being part of a rare breed, isn't it? Haha... I'm so happy that we're basically the resitance movement. It's heroic! Rah rah!";
          break;
        case 9:
          $percentage_report = "It's just me an you, sir. I'd appreciate it if you'd stop making me write these reports. I... I can see you in your side of the tent, not reading them.";
          break;
        case 10:
          $percentage_report = "There's literally no one on your side. Who are you even? Because you're not on the board. For that matter, who wrote these?!";
          break;
      }
      $report .= PHP_EOL . $percentage_report;

      $soc_speed = end($this->social_speed);
      if ($soc_speed > 0) {
        $report .= PHP_EOL . "We're losing ground to those insane anti-establishmentarians! We have to convince people that being static is correct.";
      } elseif ($soc_speed === 0) {
        $report .= PHP_EOL . "Today is pretty close to identical to yesterday, sir! It appears that change isn't as great as the revolutionaries thought!";
      } else {
        $report .= PHP_EOL . "Every day we gain more supporters! As we should, being this right.";
      }
      $soc_acc = end($this->social_acceleration);
      if ($soc_acc > 0) {
        $report .= PHP_EOL . "There is rumbling- politics currently favor the revolution.";
      } else if ($soc_acc === 0) {
        $report .= PHP_EOL . "The political world is stagnant! Which is EXACTLY how we like it!";
      } else {
        $report .= PHP_EOL . "We should hold elections while the tide of our favor is rising! So many people remain as they always were!";
      }

    /*
     * Thus begins the resistance reporting
     */
    } else {
      $report .= "To Those Who Think Freely" . PHP_EOL;
      $report .= "The Time For Change Has Come! Long Live Dynamism!" . PHP_EOL;
      $percentage_report = '';
      switch (floor($unrest_percentage / 10)) {
        case 0:
          $percentage_report = "It would be difficult to foment anything with this group of sloths. They dream in black and white, and eat Kellog's for breakfast.";
          break;
        case 1:
          $percentage_report = "There are some very provocative book clubs, but we're a long way from any serious action.";
          break;
        case 2:
          $percentage_report = "We have some promising rebel missions in the works! Ding-dong ditch on government bigwigs!";
          break;
        case 3:
          $percentage_report = "In an elevator I overheard people discussing our ideas. We're gaining traction!";
          break;
        case 4:
          $percentage_report = "Almost half of the people are on our side! We have weapons! We have folks! We have ourselves!";
          break;
        case 5:
          $percentage_report = "We are at war now! Blood is being spilt that our children will be able to see colors and write good books and stuff!";
          break;
        case 6:
          $percentage_report = "This resistance is so popular, we're basically the new government!";
          break;
        case 7:
          $percentage_report = "Well, uh, not as much fighting when you're this popular. Mostly paperwork.  Huh.  This is kinda boring. But Hooray!";
          break;
        case 8:
          $percentage_report = "We're way, way ahead. I saw one of the former heads of the continent bagging groceries.";
          break;
        case 9:
          $percentage_report = "If we get any more popular, we'll beat Friends in twitter followers! Haha, I'm just kidding. This is the future and I don't know what those things mean any more!";
          break;
        case 10:
          $percentage_report = "Literally everyone is part of the resistance now! We should definitely think up a new name!";
          break;
      }
      $report .= PHP_EOL . $percentage_report . PHP_EOL . PHP_EOL;

      $soc_speed = end($this->social_speed);
      if ($soc_speed > 0) {
        $report .= "We are gaining ground! Viva la revolucion. Revolusion? Gosh, I should really review my Spanish.... No, sir, I'm positive it's Spanish.";
      } elseif ($soc_speed === 0) {
        $report .= "No one is doing anything! Today is very similar to yesterday! We're losing!";
      } else {
        $report .= "Alas, we lose ground to the static government pigs! Blech! It's disgisting how people act!";
      }

      $report .= PHP_EOL . PHP_EOL;
      
      $soc_acc = end($this->social_acceleration);
      if ($soc_acc > 0) {
        $report .= "We have gained some political ground! The people think longer and harder on our ideas than they did before!";
      } elseif ($soc_acc === 0) {
        $report .= "Gah! Poltics is completely stagnant! The future looks bleak!";
      } else {
        $report .= "We're losing ground politically! Tell your bookmakers to write better books! Or your authors! Someone!";
      }
    }
    return $report . PHP_EOL;
  }
}
