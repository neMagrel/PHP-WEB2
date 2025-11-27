<?php
function classifyAge(int $age): string {
    if ($age < 12){
        return "Ребёнок"
    }
    else if 12 < $age <= 17{
        return "Подросток"
    }

    else: return "Взрослый"
}
$cities = []
function fiveCities(array $cities): void {

}