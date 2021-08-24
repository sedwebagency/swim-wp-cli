<?php

use function WP_CLI\Utils\make_progress_bar;

class Swim_WP_CLI extends WP_CLI_Command {
	public function test() {
	}
}

WP_CLI::add_command( 'swim', 'Swim_WP_CLI' );
