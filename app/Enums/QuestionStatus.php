<?php

namespace App\Enums;

enum QuestionStatus: string
{
    case Pending = 'pending'; // Creata, ma non posta
    case Active = 'active';   // Timer in corso, i giocatori possono prenotarsi
    case Buzzed = 'buzzed';   // Un giocatore si è prenotato, il timer è in pausa
    case Closed = 'closed';   // Domanda conclusa (con o senza vincitore)
}
