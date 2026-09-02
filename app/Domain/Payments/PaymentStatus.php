<?php

namespace App\Domain\Payments;

enum PaymentStatus: string
{
    case Created = 'CREATED';
    case WaitingForPayment = 'WAITING_FOR_PAYMENT';
    case TransactionDetected = 'TRANSACTION_DETECTED';
    case Confirming = 'CONFIRMING';
    case Confirmed = 'CONFIRMED';
    case Credited = 'CREDITED';
    case Expired = 'EXPIRED';
    case Underpaid = 'UNDERPAID';
    case Overpaid = 'OVERPAID';
    case WrongAsset = 'WRONG_ASSET';
    case WrongNetwork = 'WRONG_NETWORK';
    case Failed = 'FAILED';
    case Cancelled = 'CANCELLED';

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Created => [self::WaitingForPayment, self::Cancelled, self::Failed],
            self::WaitingForPayment => [
                self::TransactionDetected,
                self::Expired,
                self::Cancelled,
                self::WrongAsset,
                self::WrongNetwork,
            ],
            self::TransactionDetected => [
                self::Confirming,
                self::Underpaid,
                self::Overpaid,
                self::WrongAsset,
                self::WrongNetwork,
                self::Failed,
            ],
            self::Confirming => [
                self::Confirmed,
                self::Overpaid,
                self::Underpaid,
                self::Failed,
            ],
            self::Confirmed => [self::Credited, self::Overpaid, self::Failed],
            self::Overpaid => [self::Credited],
            self::Underpaid => [self::TransactionDetected, self::Cancelled, self::Expired, self::Failed],
            self::Credited, self::Expired, self::WrongAsset, self::WrongNetwork, self::Failed, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), true);
    }

    public function isTerminal(): bool
    {
        return $this->allowedTransitions() === [];
    }

    public function isSuccess(): bool
    {
        return $this === self::Credited;
    }
}
