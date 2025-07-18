export default function SecondaryButton({
    type = 'button',
    className = '',
    disabled,
    children,
    ...props
}) {
    return (
        <button
            {...props}
            type={type}
            className={ `btn btn-outline` + className }
            disabled={disabled}
        >
            {children}
        </button>
    );
}
