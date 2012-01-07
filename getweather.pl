#!/usr//bin/perl

use LWP::Simple;

my $forecast = get("http://forecast.weather.gov/MapClick.php?map.x=188&map.y=82&site=fwd&TextType=2");

my @lines = split(/\n/,$forecast);

my $foundit = "n";

open (OUTFILE, ">/var/www/WX/forecast.html");

foreach (@lines) {

	if ($_ =~ /We apologize for any inconvenience/) {
		close(OUTFILE);
		open (OUTFILE, ">/var/www/WX/forecast.html");
		print OUTFILE "&nbsp;\n";
		close (OUTFILE);
		exit;
	}
	
	if ($foundit eq "n" && ($_ =~ /<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">/)) {
		$foundit = "y";
		print OUTFILE "<table width=\"700\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\">\n";
		next;
	}

	if ($foundit eq "y" && !($_ =~ /<\/table>/)) {
		print OUTFILE "$_\n";
	}

	if ($foundit eq "y" && ($_ =~ /<\/table>/)) {
		$foundit = "n";
		print OUTFILE "$_\n";
	}

}

close (OUTFILE);
