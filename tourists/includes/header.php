<header class="main-header">
        <h1><i class="fas fa-globe-americas me-2"></i> TravelPackage Management System</h1>
    </header>
<style>

.main-header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            padding: 2rem 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .main-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(255, 255, 255, 0.1) 10px,
                rgba(255, 255, 255, 0.1) 20px
            );
            animation: move 20s linear infinite;
            z-index: 1;
        }

        @keyframes move {
            0% {
                transform: translate(0, 0);
            }
            100% {
                transform: translate(50%, 50%);
            }
        }

        .header-content {
            position: relative;
            z-index: 2;
        }

        .main-header h1 {
            color: #ffffff;
            font-size: 2.5rem;
            font-weight: 600;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            letter-spacing: 1px;
            display: inline-block;
            padding: 10px 20px;
            border-radius: 50px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }

        .main-header i {
            font-size: 2rem;
            margin-right: 15px;
            vertical-align: middle;
        }

        @media (max-width: 768px) {
            .main-header h1 {
                font-size: 1.8rem;
            }
            .main-header i {
                font-size: 1.5rem;
            }
        }
    </style>

   

