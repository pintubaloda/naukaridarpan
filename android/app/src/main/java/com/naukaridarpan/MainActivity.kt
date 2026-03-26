package com.naukaridarpan

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.foundation.layout.padding
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.*
import androidx.core.splashscreen.SplashScreen.Companion.installSplashScreen
import androidx.navigation.NavDestination.Companion.hierarchy
import androidx.navigation.NavGraph.Companion.findStartDestination
import androidx.navigation.compose.*
import com.naukaridarpan.ui.theme.NaukaridarpanTheme
import dagger.hilt.android.AndroidEntryPoint

sealed class Screen(val route: String) {
    object Home       : Screen("home")
    object Browse     : Screen("browse")
    object MyExams    : Screen("my_exams")
    object Results    : Screen("results")
    object Profile    : Screen("profile")
    object Login      : Screen("login")
    object Register   : Screen("register")
    object ExamDetail : Screen("exam_detail/{slug}")
    object TakeExam   : Screen("take_exam/{purchaseId}/{examUrl}")
    object Blog       : Screen("blog")
    object BlogDetail : Screen("blog_detail/{slug}")
}

val bottomNavItems = listOf(
    Triple(Screen.Home,    Icons.Default.Home,       "Home"),
    Triple(Screen.Browse,  Icons.Default.Search,     "Browse"),
    Triple(Screen.MyExams, Icons.Default.LibraryBooks,"My Exams"),
    Triple(Screen.Results, Icons.Default.BarChart,   "Results"),
    Triple(Screen.Profile, Icons.Default.Person,     "Profile"),
)

@AndroidEntryPoint
class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        installSplashScreen()
        super.onCreate(savedInstanceState)
        setContent {
            NaukaridarpanTheme {
                NaukaridarpanApp()
            }
        }
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun NaukaridarpanApp() {
    val navController = rememberNavController()
    val currentBackStack by navController.currentBackStackEntryAsState()
    val currentDestination = currentBackStack?.destination

    // Hide bottom nav during exam
    val showBottomBar = currentDestination?.route?.startsWith("take_exam") == false

    Scaffold(
        bottomBar = {
            if (showBottomBar) {
                NavigationBar {
                    bottomNavItems.forEach { (screen, icon, label) ->
                        NavigationBarItem(
                            icon  = { Icon(icon, label) },
                            label = { Text(label, fontSize = androidx.compose.ui.unit.TextUnit(11f, androidx.compose.ui.unit.TextUnitType.Sp)) },
                            selected = currentDestination?.hierarchy?.any { it.route == screen.route } == true,
                            onClick = {
                                navController.navigate(screen.route) {
                                    popUpTo(navController.graph.findStartDestination().id) { saveState = true }
                                    launchSingleTop = true
                                    restoreState = true
                                }
                            },
                            colors = NavigationBarItemDefaults.colors(
                                selectedIconColor   = com.naukaridarpan.ui.theme.Saffron,
                                selectedTextColor   = com.naukaridarpan.ui.theme.Saffron,
                                indicatorColor      = com.naukaridarpan.ui.theme.SaffronLight,
                                unselectedIconColor = com.naukaridarpan.ui.theme.InkLight,
                                unselectedTextColor = com.naukaridarpan.ui.theme.InkLight,
                            )
                        )
                    }
                }
            }
        }
    ) { paddingValues ->
        NavHost(
            navController   = navController,
            startDestination = Screen.Home.route,
            modifier        = Modifier.padding(paddingValues),
        ) {
            composable(Screen.Home.route)   { /* HomeScreen ViewModel */ }
            composable(Screen.Browse.route) { /* BrowseScreen ViewModel */ }
            composable(Screen.MyExams.route){ /* MyExamsScreen ViewModel */ }
            composable(Screen.Results.route){ /* ResultsScreen ViewModel */ }
            composable(Screen.Profile.route){ /* ProfileScreen ViewModel */ }
            composable(Screen.Login.route)  { /* LoginScreen ViewModel */ }
            composable(Screen.Register.route){ /* RegisterScreen ViewModel */ }
            composable(Screen.ExamDetail.route){ backStack ->
                val slug = backStack.arguments?.getString("slug") ?: ""
                /* ExamDetailScreen ViewModel with slug */
            }
            composable(Screen.TakeExam.route){ backStack ->
                val purchaseId = backStack.arguments?.getString("purchaseId")?.toIntOrNull() ?: 0
                val examUrl    = backStack.arguments?.getString("examUrl") ?: ""
                /* TakeExamScreen */
            }
            composable(Screen.Blog.route)   { /* BlogScreen ViewModel */ }
            composable(Screen.BlogDetail.route){ /* BlogDetailScreen ViewModel */ }
        }
    }
}
