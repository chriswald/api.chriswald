<?php

function SetW(&$data, $newVal)
{
    RegSet($data, "W", $newVal);
}

function GetW($data)
{
    return RegGet($data, "W");
}

function SetF(&$data, $newVal)
{
    $data["F"] = $newVal;
}

function GetF($data)
{
    return $data["F"];
}

function GetD($data)
{
    return RegGet($data, "D", 0x02);
}

function GetK($data)
{
    return RegGet($data, "K");
}

function GetB($data)
{
    return RegGet($data, "B", 0x08);
}

function RegGet($data, $reg, $mod = 0x100)
{
    return +$data[$reg] % $mod;
}

function RegSet(&$data, $reg, &$newVal, $mod = 0x100)
{
    $data[$reg] = (+$newVal % $mod);
}

function RegNameToAddress($name)
{
    $address = $name;
    $registers = array(
        "TMR0"    => "0x01",
        "PCL"     => "0x02",
        "STATUS"  => "0x03",
        "FSR"     => "0x04",
        "PORTA"   => "0x05",
        "PORTB"   => "0x06",
        "PORTC"   => "0x07",
        "PORTD"   => "0x08",
        "PORTE"   => "0x09",
        "PCLATH"  => "0x0A",
        "INTCON"  => "0x0B",
        "PIR1"    => "0x0C",
        "PIR2"    => "0x0D",
        "TMR1L"   => "0x0E",
        "TMR1H"   => "0x0F",
        "T1CON"   => "0x10",
        "TMR2"    => "0x11",
        "T2CON"   => "0x12",
        "SSPBUF"  => "0x13",
        "SSPCON"  => "0x14",
        "CCPR1L"  => "0x15",
        "CCPR1H"  => "0x16",
        "CCP1CON" => "0x17",
        "RCSTA"   => "0x18",
        "TXREG"   => "0x19",
        "RCREG"   => "0x1A",
        "CCPR2L"  => "0x1B",
        "CCPR2H"  => "0x1C",
        "CCP2CON" => "0x1D",
        "ADRESH"  => "0x1E",
        "ADCON0"  => "0x1F"
    );

    if (array_key_exists($name, $registers))
    {
        $address = $registers[$name];
    }

    return $address;
}

function AddressToRegName($address)
{
    $name = "" + dechex($address);
    $registers = array(
        "0x01" => "TMR0",
        "0x02" => "PCL",
        "0x03" => "STATUS",
        "0x04" => "FSR",
        "0x05" => "PORTA",
        "0x06" => "PORTB",
        "0x07" => "PORTC",
        "0x08" => "PORTD",
        "0x09" => "PORTE",
        "0x0A" => "PCLATH",
        "0x0B" => "INTCON",
        "0x0C" => "PIR1",
        "0x0D" => "PIR2",
        "0x0E" => "TMR1L",
        "0x0F" => "TMR1H",
        "0x10" => "T1CON",
        "0x11" => "TMR2",
        "0x12" => "T2CON",
        "0x13" => "SSPBUF",
        "0x14" => "SSPCON",
        "0x15" => "CCPR1L",
        "0x16" => "CCPR1H",
        "0x17" => "CCP1CON",
        "0x18" => "RCSTA",
        "0x19" => "TXREG",
        "0x1A" => "RCREG",
        "0x1B" => "CCPR2L",
        "0x1C" => "CCPR2H",
        "0x1D" => "CCP2CON",
        "0x1E" => "ADRESH",
        "0x1F" => "ADCON0"
    );

    if (array_key_exists($address, $registers))
    {
        $name = $registers[$address];
    }

    return $name;
}

function SetStatusZero(&$data)
{
    $status = +RegGet($data, "STATUS");
    $status = $status | 0x04;
    RegSetInternal($data, "STATUS", $status);
}

function ClrStatusZero(&$data)
{
    $status = +RegGet($data, "STATUS");
    $status = $status & ~0x04;
    RegSetInternal($data, "STATUS", $status);
}

function SetStatusDigitCarry(&$data)
{
    $status = +RegGet($data, "STATUS");
    $status = $status | 0x02;
    RegSetInternal($data, "STATUS", $status);
}

function ClrStatusDigitCarry(&$data)
{
    $status = +RegGet($data, "STATUS");
    $status = $status & ~0x02;
    RegSetInternal($data, "STATUS", $status);
}

function SetStatusCarry(&$data)
{
    $status = +RegGet($data, "STATUS");
    $status = $status | 0x01;
    RegSetInternal($data, "STATUS", $status);
}

function ClrStatusCarry(&$data)
{
    $status = +RegGet($data, "STATUS");
    $status = $status & ~0x01;
    RegSetInternal($data, "STATUS", $status);
}

function GetStatusCarry($data)
{
    $status = +RegGet($data, "STATUS");
    return $status & 0x01;
}

?>
