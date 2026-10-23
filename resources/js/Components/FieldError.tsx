type FieldErrorProps = {
    message?: string;
};

export default function FieldError({ message }: FieldErrorProps) {
    return message ? (
        <p role="alert" className="mt-1 text-sm text-red-600">
            {message}
        </p>
    ) : null;
}
