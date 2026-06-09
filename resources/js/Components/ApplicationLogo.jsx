export default function ApplicationLogo({ className = '', ...props }) {
    return (
        <svg
            {...props}
            className={className}
            viewBox="0 0 48 48"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true"
        >
            <rect width="48" height="48" rx="14" fill="currentColor" />
            <path
                d="M13 15.5C16.8 14.4 20.2 15.1 24 17.8V35C20.4 32.5 16.8 31.7 13 32.8V15.5Z"
                fill="white"
                fillOpacity="0.94"
            />
            <path
                d="M35 15.5C31.2 14.4 27.8 15.1 24 17.8V35C27.6 32.5 31.2 31.7 35 32.8V15.5Z"
                fill="white"
                fillOpacity="0.72"
            />
            <path
                d="M17 20.5C18.9 20.4 20.4 20.9 22 21.9M17 24.5C18.9 24.4 20.4 24.9 22 25.9"
                stroke="#312E81"
                strokeWidth="1.8"
                strokeLinecap="round"
            />
            <path
                d="M26 21.9C27.6 20.9 29.1 20.4 31 20.5M26 25.9C27.6 24.9 29.1 24.4 31 24.5"
                stroke="#312E81"
                strokeWidth="1.8"
                strokeLinecap="round"
            />
        </svg>
    );
}
