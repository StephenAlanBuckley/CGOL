<?php
require_once("world.php");

if ($argc < 3 || strpos($argv[1], "h") !== false) {

?>
Welcome to 1984! The perfect world simulator that is totally in the future.
For a world leader, you seem confused, so here's how to get your society goin'
strong!

  Usage:
  <?php echo $argv[0]; ?> <width> <height> <filename> <generations> <verbose> <really_quite_verbose>

  <width> can be any non-zero width for your neighborhood.
  Though widths lower than 10 are PRETTY boring, even by 
  Orwellian standards.

  <height> can be any non-zero height for your neighborhood.
  Again, gotta recommend that you go at least for 10 height.
  Not much of a society otherwise...

  <filename> will help kickstart your world building. If <filename>
  doesn't exist as a .txt file in the current directory yet then
  <?php echo $argv[0]; ?> will make an empty world for you to play with!
  If <filename> already exists then <?php echo $argv[0]; ?> will try to 
  use it as a world! (It's worth noting that this is only meant to work
  with files in the same format as the original creation...)

  --Optional Parameters--
  <generations> will determine how many generations your program will run
  for. May your reign last a hundred thousand years!

  <verbose> if set to 1 will show you each generation as it happens.
  By default only the last generation will be shown and saved to file.

  <really_quite_verbose> is. Settings are 1 and 2. Defaults to off

<?php
} else {
  $width = $argv[1];
  $height = $argv[2];
  $world_source = $argv[3];

  $world_path = (__DIR__ . DIRECTORY_SEPARATOR . $world_source . '.txt');

  $brave = new World($width,$height);
  if (!file_exists($world_path)) {
    $world_file = fopen($world_path, "w");
    fwrite($world_file, $brave->get_state_diagram());
    fclose($world_file);
  } else {
    $brave->initialize_with_file($world_path);
    $generations = 20;
    if ($argc > 4) {
      $generations = (int)$argv[4];
    }
    $verbose = false;
    if ($argc > 5) {
      if ($argv[5] == 1) {
        $verbose = true;
      }
    }

    $reporting = 0;
    if ($argc > 6) {
      $reporting = $argv[6];
    }
    $diagram = "";
    $delimiter = "";
    for ($x = 1; $x < 2 * $width; $x++) {
      $delimiter .= "-";
    }

    $new_path = (__DIR__ . DIRECTORY_SEPARATOR . $world_source . "-" . time() . ".txt");
    $write_file = fopen($new_path, "w");
    fwrite($write_file, "Width: " . $width . "  Height: " . $height . PHP_EOL);
    for ($generation = 0; $generation <= $generations; $generation++) {
      $diagram = $brave->get_state_diagram();
      if ($verbose) {
        $report = "";
        if ($reporting !== 0) {
          $report = $brave->report($reporting);
        }
        print_r($brave->get_state_diagram());
        print_r($report);
        print_r($delimiter . $generation . PHP_EOL);
        fwrite($write_file, $diagram);
        fwrite($write_file, $report);
        fwrite($write_file, $delimiter . $generation . PHP_EOL);
      }
      $brave->next_generation();
    }
    if (!$verbose) {
      fwrite($write_file, $diagram);
    }
    $command_line_command = "php ";
    for ($i = 0; $i < $argc; $i++) {
      $command_line_command .= ($argv[$i] . " ");
    }
    fwrite($write_file, "Width: " . $width . "  Height: " . $height . PHP_EOL);
    fwrite($write_file, "To run this again:" . PHP_EOL . $command_line_command . PHP_EOL);
    fclose($write_file);
  }
}

