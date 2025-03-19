<?php

class Platezhka
{
    public string $date;
    public float $sum;
    public string $paydirection;
    public Kontragent $payer;
    public Kontragent $receiver;
}

?>