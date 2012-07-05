#!/usr//bin/perl

use LWP::Simple;

my $forecast = get("http://forecast.weather.gov/MapClick.php?map.x=188&map.y=82&site=fwd&TextType=2");

my @lines = split(/\n/,$forecast);

my $foundit = "n";
my $filename = "/var/www/WX/forecast.html";

open (OUTFILE, ">$filename");

foreach (@lines) {

	if ($_ =~ /We apologize for any inconvenience/) {
		close(OUTFILE);
		open (OUTFILE, ">/var/www/WX/forecast.html");
		print OUTFILE "&nbsp;\n";
		close (OUTFILE);
		exit;
	}
	
	if ($foundit eq "n" && ($_ =~ /<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">(.*)<\/table>/)) {
		print OUTFILE "<table width=\"700\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\">\n";
		print OUTFILE $1;
		print OUTFILE "\n</table>\n";
	}
}

close (OUTFILE);

# What we are doing here could probably be done inside perl, but I was
# lazy and just used SED for this.
$fixedoutput = `/bin/sed s_/images/_http://forecast.weather.gov/images/_g < /var/www/WX/forecast.html`;

open (OUTFILE, ">$filename");
print OUTFILE $fixedoutput;
close (OUTFILE);
