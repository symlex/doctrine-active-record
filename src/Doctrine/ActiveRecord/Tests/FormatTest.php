<?php

namespace Doctrine\ActiveRecord\Tests;

use TestTools\TestCase\UnitTestCase;
use Doctrine\ActiveRecord\Format;
use DateTime;

/**
 * @author Michael Mayer <michael@lastzero.net>
 * @license MIT
 */
class FormatTest extends UnitTestCase {
    public function setUp () {
        date_default_timezone_set('UTC');
    }

    public function testFromSqlDatetime () {
        $output = Format::fromSql(Format::DATETIME, '2010-10-11 17:08:21');
        $this->assertInstanceOf('\DateTime', $output);
        $this->assertEquals('11.10.2010 17:08', $output->format('d.m.Y H:i'));
    }

    public function testFromSqlDate () {
        $output = Format::fromSql(Format::DATE, '2010-10-11');
        $this->assertInstanceOf('\DateTime', $output);
        $this->assertEquals('11.10.2010', $output->format('d.m.Y'));
        $this->assertEquals('00:00:00', $output->format('H:i:s'));
    }

    public function testFromSqlTime () {
        $output = Format::fromSql(Format::TIME, '17:08:21');
        $this->assertInstanceOf('\DateTime', $output);
        $this->assertEquals('17:08:21.000000', $output->format('H:i:s.u'));

        $output = Format::fromSql(Format::TIMEU, '17:08:21.123456');
        $this->assertInstanceOf('\DateTime', $output);
        $this->assertEquals('17:08:21.123456', $output->format('H:i:s.u'));

        $output = Format::fromSql(Format::TIMEU, '11:09:21');
        $this->assertInstanceOf('\DateTime', $output);
        $this->assertEquals('11:09:21.000000', $output->format('H:i:s.u'));

        $output = Format::fromSql(Format::TIMEUTZ, '11:09:21.123456+0130');
        $this->assertInstanceOf('\DateTime', $output);
        $this->assertEquals('11:09:21.123456+0130', $output->format('H:i:s.uO'));
    }

    public function testFromSqlDatetimeu () {
        $output = Format::fromSql(Format::DATETIMEU, '2015-07-02 15:23:47.267367');
        $this->assertInstanceOf('\DateTime', $output);
        $this->assertEquals('02.07.2015', $output->format('d.m.Y'));
        $this->assertEquals('15:23:47', $output->format('H:i:s'));
        $this->assertEquals('267367', $output->format('u'));

        $output = Format::fromSql(Format::DATETIMEU, '2015-07-02 15:23:47');
        $this->assertInstanceOf('\DateTime', $output);
        $this->assertEquals('02.07.2015', $output->format('d.m.Y'));
        $this->assertEquals('15:23:47', $output->format('H:i:s'));
        $this->assertEquals('000000', $output->format('u'));
    }

    public function testFromSqlDatetimeTimezeone () {
        $output = Format::fromSql(Format::DATETIMEUTZ, '2015-07-02 15:23:47.267367+02');
        $this->assertInstanceOf('\DateTime', $output);
        $this->assertEquals('02.07.2015', $output->format('d.m.Y'));
        $this->assertEquals('15:23:47', $output->format('H:i:s'));
        $this->assertEquals('267367', $output->format('u'));
        $this->assertEquals('+0200', $output->format('O'));

        $output = Format::fromSql(Format::DATETIMETZ, '2015-07-02 15:23:47-0530');
        $this->assertInstanceOf('\DateTime', $output);
        $this->assertEquals('02.07.2015', $output->format('d.m.Y'));
        $this->assertEquals('15:23:47', $output->format('H:i:s'));
        $this->assertEquals('000000', $output->format('u'));
        $this->assertEquals('-0530', $output->format('O'));
    }

    public function testToSqlDatetimeuFromLocaleFormat () {
        $output = Format::toSql(Format::DATETIMEU, '11.10.2015 15:23:47.267367');
        $this->assertEquals('2015-10-11 15:23:47.267367', $output);
    }

    public function testToSqlDatetimeuFromLocaleDatetimeFormat () {
        $output = Format::toSql(Format::DATETIMEU, '11.10.2015 15:23:47');
        $this->assertEquals('2015-10-11 15:23:47.000000', $output);
    }

    public function testFromSqlUnixTimestamp () {
        $output = Format::fromSql(Format::TIMESTAMP, '1354632469');
        $this->assertInstanceOf('\DateTime', $output);
        $this->assertEquals('04.12.2012', $output->format('d.m.Y'));

        $output = Format::fromSql(Format::TIMESTAMP, 1354632469);
        $this->assertInstanceOf('\DateTime', $output);
        $this->assertEquals('04.12.2012', $output->format('d.m.Y'));

        $output = Format::fromSql(Format::TIMESTAMP, '1354632469');
        $this->assertInstanceOf('\DateTime', $output);
        $this->assertEquals('04.12.2012 14:47', $output->format('d.m.Y H:i'));

        $output = Format::fromSql(Format::TIMESTAMP, 1354632469);
        $this->assertInstanceOf('\DateTime', $output);
        $this->assertEquals('04.12.2012 14:47', $output->format('d.m.Y H:i'));
    }

    public function testToSqlDateException () {
        $this->setExpectedException('Doctrine\ActiveRecord\Exception\FormatException');
        Format::toSql(Format::DATE, new Format());
    }

    public function testToSqlDatetimeException () {
        $this->setExpectedException('Doctrine\ActiveRecord\Exception\FormatException');
        Format::toSql(Format::DATETIME, new Format());
    }

    public function testFromSqlNumberException () {
        $this->setExpectedException('Doctrine\ActiveRecord\Exception\FormatException');
        Format::fromSql('#.00', 1234);
    }

    public function testToSqlDateFromEmptyValue () {
        $output = Format::toSql(Format::DATE, '');
        $this->assertEquals(null, $output);

        $output = Format::toSql(Format::DATE, null);
        $this->assertEquals(null, $output);

        $output = Format::toSql(Format::DATE, 0);
        $this->assertEquals(null, $output);
    }

    public function testToSqlDateFromLocaleFormat () {
        $output = Format::toSql(Format::DATE, '11.10.2010');
        $this->assertEquals('2010-10-11', $output);
    }

    public function testToSqlDateFromDbFormat () {
        $output = Format::toSql(Format::DATE, '2010-10-11');
        $this->assertEquals('2010-10-11', $output);
    }

    public function testToSqlDateFromDateTime () {
        $date = new DateTime('2010-10-11');
        $output = Format::toSql(Format::DATE, $date);
        $this->assertEquals('2010-10-11', $output);

        $date = new DateTime('11.10.2010');
        $output = Format::toSql(Format::DATE, $date);
        $this->assertEquals('2010-10-11', $output);
    }

    public function testToSqlTimestampFromEmptyValue () {
        $output = Format::toSql(Format::TIMESTAMP, '');
        $this->assertEquals(null, $output);

        $output = Format::toSql(Format::TIMESTAMP, null);
        $this->assertEquals(null, $output);

        $output = Format::toSql(Format::TIMESTAMP, 0);
        $this->assertEquals(null, $output);
    }

    public function testToSqlTimestampFromLocaleFormat() {
        $output = Format::toSql(Format::TIMESTAMP, '04.12.2012');
        $this->assertEquals(1354579200, $output);
    }

    public function testToSqlTimestampFromDateTime() {
        $date = new DateTime('2012-12-04');
        $output = Format::toSql(Format::TIMESTAMP, $date);
        $this->assertEquals(1354579200, $output);

        $date = new DateTime('04.12.2012');
        $output = Format::toSql(Format::TIMESTAMP, $date);
        $this->assertEquals(1354579200, $output);
    }

    public function testToSqlDatetimeFromEmptyValue () {
        $output = Format::toSql(Format::DATETIME, '');
        $this->assertEquals(null, $output);

        $output = Format::toSql(Format::DATETIME, null);
        $this->assertEquals(null, $output);

        $output = Format::toSql(Format::DATETIME, 0);
        $this->assertEquals(null, $output);
    }

    public function testToSqlDatetimeFromLocaleFormat () {
        $output = Format::toSql(Format::DATETIME, '11.10.2010 18:34:45');
        $this->assertEquals('2010-10-11 18:34:45', $output);
    }

    public function testToSqlDatetimeFromDbFormat () {
        $output = Format::toSql(Format::DATETIME, '2010-10-11 18:34:45');
        $this->assertEquals('2010-10-11 18:34:45', $output);
    }

    public function testToSqlDatetimeFromDateTime () {
        $date = new DateTime('2010-10-11 18:34:45');
        $output = Format::toSql(Format::DATETIME, $date);
        $this->assertEquals('2010-10-11 18:34:45', $output);

        $date = new DateTime('11.10.2010 18:34:45');
        $output = Format::toSql(Format::DATETIME, $date);
        $this->assertEquals('2010-10-11 18:34:45', $output);
    }

    public function testToSqlTimestamptimeFromEmptyValue () {
        $output = Format::toSql(Format::TIMESTAMP, '');
        $this->assertEquals(null, $output);

        $output = Format::toSql(Format::TIMESTAMP, null);
        $this->assertEquals(null, $output);

        $output = Format::toSql(Format::TIMESTAMP, 0);
        $this->assertEquals(null, $output);
    }

    public function testToSqlTimestamptimeFromLocaleFormat () {
        $output = Format::toSql(Format::TIMESTAMP, '04.12.2012 15:47');
        $this->assertEquals(1354636020, $output);
    }

    public function testToSqlTimestamptimeFromDbFormat () {
        $output = Format::toSql(Format::TIMESTAMP, '2012-12-04 15:47');
        $this->assertEquals(1354636020, $output);
    }

    public function testToSqlTimestamptimeFromDateTime () {
        $date = new DateTime('2012-12-04 15:47');
        $output = Format::toSql(Format::TIMESTAMP, $date);
        $this->assertEquals(1354636020, $output);

        $date = new DateTime('04.12.2012 15:47');
        $output = Format::toSql(Format::TIMESTAMP, $date);
        $this->assertEquals(1354636020, $output);
    }

    public function testFromSqlFloat () {
        $output = Format::fromSql(Format::FLOAT, '11.345');
        $this->assertEquals(11.345, $output);

        $output = Format::fromSql(Format::FLOAT, 11.345);
        $this->assertEquals(11.345, $output);
    }

    public function testToSqlFloat () {
        $output = Format::toSql(Format::FLOAT, '11,345');
        $this->assertEquals(11.345, $output);

        $output = Format::toSql(Format::FLOAT, '11.345');
        $this->assertEquals(11.345, $output);

        $output = Format::toSql(Format::FLOAT, 11.345);
        $this->assertEquals(11.345, $output);
    }

    public function testToSqlAlphanumeric () {
        $output = Format::toSql(Format::ALPHANUMERIC, 'ALKDFHE 1234567890 ;"[_+)(*&^%$');
        $this->assertEquals('ALKDFHE 1234567890 _', $output);
    }

    public function testFromSqlAlphanumeric () {
        $output = Format::fromSql(Format::ALPHANUMERIC, 'ALKDFHE 1234567890 ;"[_+)(*&^%$');
        $this->assertEquals('ALKDFHE 1234567890 _', $output);
    }

    public function testToSqlNumbers () {
        $output = Format::toSql(Format::FLOAT, '11,345');
        $this->assertEquals(11.345, $output);

        $output = Format::toSql(Format::FLOAT, '12.311,345');
        $this->assertEquals(12311.345, $output);

        $output = Format::toSql(Format::FLOAT, '11.345');
        $this->assertEquals(11.345, $output);

        $output = Format::toSql(Format::FLOAT, '11345.');
        $this->assertEquals(11345, $output);

        $output = Format::toSql(Format::FLOAT, '11.345.000,12');
        $this->assertEquals(11345000.12, $output);

        $output = Format::toSql(Format::FLOAT, '11,345,000.12');
        $this->assertEquals(11345000.12, $output);

        $output = Format::toSql(Format::FLOAT, 11.345);
        $this->assertEquals(11.345, $output);
    }

    public function testFromSqlNumbers () {
        $output = Format::fromSql(Format::FLOAT, 840293411.3450);
        $this->assertEquals('840293411.345', $output);

        $output = Format::fromSql(Format::FLOAT, 11.345);
        $this->assertEquals('11.345', $output);

        $output = Format::fromSql(Format::FLOAT, 1.345);
        $this->assertEquals('1.345', $output);
    }

    public function testToSqlJSON () {
        $output = Format::toSql(Format::JSON, array('foo' => 'bar'));
        $this->assertEquals(array('foo' => 'bar'), json_decode($output, true));
    }

    public function testFromSqlJSON () {
        $output = Format::toSql(Format::JSON, array('foo' => 'bar'));
        $output = Format::fromSql(Format::JSON, $output);
        $this->assertEquals(array('foo' => 'bar'), $output);
    }

    public function testToSqlSerialized () {
        $output = Format::toSql(Format::SERIALIZED, array('foo' => 'bar'));
        $this->assertEquals(array('foo' => 'bar'), unserialize($output));
    }

    public function testFromSqlSerialized () {
        $output = Format::toSql(Format::SERIALIZED, array('foo' => 'bar'));
        $output = Format::fromSql(Format::SERIALIZED, $output);
        $this->assertEquals(array('foo' => 'bar'), $output);
    }
}