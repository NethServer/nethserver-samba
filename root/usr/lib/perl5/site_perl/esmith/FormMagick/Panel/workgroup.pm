#!/usr/bin/perl -w 

#
# $Id: workgroup.pm,v 1.4 2002/05/28 19:22:13 skud Exp $
#

package    esmith::FormMagick::Panel::workgroup;

use strict;
use esmith::ConfigDB;
use esmith::FormMagick;
use esmith::util;
use File::Basename;
use Exporter;
use Carp;

our @ISA = qw(esmith::FormMagick Exporter);

our @EXPORT = qw( validate_workgroup validate_servername change_settings
		  get_value get_prop 
);



our $VERSION = sprintf '%d.%03d', q$Revision: 1.4 $ =~ /: (\d+).(\d+)/;
our $db = esmith::ConfigDB->open;


# {{{ header

=pod 

=head1 NAME

esmith::FormMagick::Panels::workgroup - useful panel functions

=head1 SYNOPSIS

    use esmith::FormMagick::Panels::workgroup;

    my $panel = esmith::FormMagick::Panel::workgroup->new();
    $panel->display();

=head1 DESCRIPTION

=cut

# {{{ new

=head2 new();

Exactly as for esmith::FormMagick

=begin testing


use_ok('esmith::FormMagick::Panel::workgroup');
use vars qw($panel);
ok($panel = esmith::FormMagick::Panel::workgroup->new(), "Create panel object");
isa_ok($panel, 'esmith::FormMagick::Panel::workgroup');

=end testing

=cut



sub new {
    shift;
    my $self = esmith::FormMagick->new();
    $self->{calling_package} = (caller)[0];
    bless $self;
    return $self;
}

# }}}

=head2 get_prop ITEM PROP

A simple accessor for esmith::ConfigDB::Record::prop

=cut

sub get_prop {
  my $fm = shift;
  my $item = shift;
  my $prop = shift;
  return $db->get($item)->prop($prop);
}

=head2 get_value ITEM

A simple accessor for esmith::ConfigDB::Record::value

=cut

sub get_value {
  my $fm = shift;
  my $item = shift;
  return ($db->get('smb')->prop($item));
}

# {{{ Validation

=head1 VALIDATION ROUTINES

=head2 validate_servername

Returns OK if servername is valid. 

Returns servername_VALIDATION_ERROR and pushes us back to the first page otherwise.

=begin testing

is(validate_servername('','foo_com'), 'OK', 'foo.com is not a valid host');
isnt(validate_servername('','foo.com'), 'OK', 'foo.com is not a valid host');
ok(validate_servername('','') eq 'INVALID_SERVERNAME', 'undef is not a valid host');
ok(validate_servername('','flees ble') eq 'INVALID_SERVERNAME', '"flees ble" is not a valid host');


=end testing

=cut

sub validate_servername {
    my $fm = shift;
    my $servername = shift;
    
    return ('OK') if ( $servername =~ /^([a-zA-Z][\-\w]*)$/ );
    
    return "INVALID_SERVERNAME";
}

=head2 validate_workgroup

Returns OK if workgroup is valid. 

Returns workgroup_VALIDATION_ERROR and pushes us back to the first page otherwise.

=begin testing

$panel->{cgi} = CGI->new("");
ok(validate_workgroup($panel,'foo.com') eq 'OK', 'foo.com is a valid host');
ok(validate_workgroup($panel,'') eq 'INVALID_WORKGROUP', 'undef is not a valid host');
ok(validate_workgroup($panel,'flees ble') eq 'INVALID_WORKGROUP', '"flees ble" is not a valid host');


=end testing

=cut

sub validate_workgroup {
    my $fm = shift;
    my $workgroup = lc(shift);
    
    my $server = lc($fm->cgi->param('ServerName'));
    return "INVALID_WORKGROUP" unless ( $workgroup =~ /^([a-zA-Z0-9][\-\w\.]*)$/ );
    return 'INVALID_WORKGROUP_MATCHES_SERVERNAME' if ( $server eq $workgroup);
    return ('OK'); 
    
}


# }}}

=head1 ACTION

=head2 change_settings

	If everything has been validated, properly, go ahead and set the new settings

=cut



sub change_settings {
    my $self = shift;
    my $q = $self->{'cgi'};

    my $RoamingProfiles = ($q->param('RoamingProfiles') || 'no');
    my $ServerRole = ($q->param('ServerRole') || 'WS');

    $db->get('smb')->set_prop('Workgroup', $q->param('Workgroup'));
    $db->get('smb')->set_prop('ServerRole', $ServerRole);
    $db->get('smb')->set_prop('ServerName', $q->param('ServerName'));
    $db->get('smb')->set_prop("RoamingProfiles", $RoamingProfiles);

    system( "/sbin/e-smith/signal-event", "workgroup-update" ) == 0
      or return $self->error('ERROR_UPDATING');
    
    return $self->success('SUCCESS');
}


1;

